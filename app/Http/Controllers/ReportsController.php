<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct()
    {
        // All reports are financial — only admins should access
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Only administrators may access financial reports');
            }
            return $next($request);
        });
    }
    // Income for a day or date range
    public function income(Request $request)
    {
        $start = $request->query('from');
        $end = $request->query('to');

        $query = Invoice::query();
        if ($start)
            $query->whereDate('created_at', '>=', $start);
        if ($end)
            $query->whereDate('created_at', '<=', $end);

        $total = $query->sum('total');
        $paid = Payment::whereIn('invoice_id', $query->pluck('id'))->sum('amount');

        return response()->json(['total_invoiced' => $total, 'total_paid' => $paid]);
    }

    // Expenses in range
    public function expenses(Request $request)
    {
        $start = $request->query('from');
        $end = $request->query('to');
        $q = Expense::query();
        if ($start)
            $q->whereDate('incurred_on', '>=', $start);
        if ($end)
            $q->whereDate('incurred_on', '<=', $end);
        $sum = $q->sum('amount');
        return response()->json(['total_expenses' => $sum, 'breakdown' => $q->select('category', \DB::raw('SUM(amount) as total'))->groupBy('category')->get()]);
    }

    // Profit = income - expenses
    public function profit(Request $request)
    {
        $income = $this->income($request)->getData(true)['total_paid'] ?? 0;
        $expenses = $this->expenses($request)->getData(true)['total_expenses'] ?? 0;
        return response()->json(['income' => $income, 'expenses' => $expenses, 'profit' => $income - $expenses]);
    }

    // Debts (unpaid invoices)
    public function debts(Request $request)
    {
        $invoices = Invoice::whereIn('status', ['unpaid', 'partial'])->with('patient')->get();
        $data = $invoices->map(function ($invoice) {
            $paid = $invoice->payments()->sum('amount');
            $due = $invoice->total - $paid;
            return ['id' => $invoice->id, 'invoice_number' => $invoice->invoice_number, 'patient' => $invoice->patient, 'total' => $invoice->total, 'paid' => $paid, 'due' => $due];
        });
        return response()->json($data);
    }

    // Unified Financial Summary
    public function financialSummary(Request $request)
    {
        $start = $request->query('from');
        $end = $request->query('to');

        // 1. Income (Collected Payments)
        $incomeQ = Payment::query();
        if ($start)
            $incomeQ->whereDate('payments.created_at', '>=', $start);
        if ($end)
            $incomeQ->whereDate('payments.created_at', '<=', $end);
        $totalCollected = $incomeQ->sum('amount');

        // 2. Expenses (Operational)
        $expensesQ = Expense::query();
        if ($start)
            $expensesQ->whereDate('expenses.incurred_on', '>=', $start);
        if ($end)
            $expensesQ->whereDate('expenses.incurred_on', '<=', $end);
        $opExpenses = $expensesQ->sum('amount');

        // 3. Inventory Purchases (COGS)
        // Inventory feature is currently DISABLED (non-destructive). Keep inventory cost as 0
        // to avoid triggering inventory queries while preserving data and codebase.
        $inventoryCost = 0.0;

        // 4. Lab Orders Cost
        $labQ = \App\Models\LabOrder::query();
        if ($start)
            $labQ->whereDate('lab_orders.created_at', '>=', $start);
        if ($end)
            $labQ->whereDate('lab_orders.created_at', '<=', $end);
        $labCost = $labQ->sum('cost');

        // 5. Payroll (Net Withdrawals for Cash Flow)
        $payrollQ = \App\Models\Withdrawal::query();
        if ($start)
            $payrollQ->whereDate('withdrawals.created_at', '>=', $start);
        if ($end)
            $payrollQ->whereDate('withdrawals.created_at', '<=', $end);
        $payrollCash = $payrollQ->sum('amount');

        // Per user request: Inventory Purchase Cost is DISABLED and not included in operational expenses.
        $totalExpenses = $opExpenses + $labCost + $payrollCash;

        // Net Profit excludes inventory cost since inventory module is disabled
        $netProfit = $totalCollected - $totalExpenses;

        return response()->json([
            'income' => $totalCollected,
            'expenses' => [
                'operational' => $opExpenses,
                // inventory disabled: keep key for compatibility but set to 0
                'inventory' => 0.0,
                'inventory_disabled' => true,
                'lab_orders' => $labCost,
                'payroll' => $payrollCash,
                'total' => $totalExpenses
            ],
            'net_profit' => $netProfit
        ]);
    }

    // Detailed Doctor Report (for Payouts Page)
    public function doctorReport(Request $request, $doctorId)
    {
        $start = $request->query('from');
        $end = $request->query('to');
        $doctor = \App\Models\User::findOrFail($doctorId);
        $rate = ($doctor->commission_pct ?? 0) / 100;

        // A. Invoiced Commission
        // We rely on 'invoices.doctor_id' which represents the doctor who "owns" the invoice (and thus gets commission)
        $invoicedQ = Invoice::query()
            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.doctor_id', $doctorId);

        if ($start)
            $invoicedQ->whereDate('invoices.created_at', '>=', $start);
        if ($end)
            $invoicedQ->whereDate('invoices.created_at', '<=', $end);

        $totalInvoiced = $invoicedQ->sum('invoice_items.subtotal');
        $commissionInvoiced = $totalInvoiced * $rate;

        // B. Earned Commission (Based on Payments)
        // Ensure we filter payments related to invoices owned by this doctor
        $paidQ = Payment::query()
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->where('invoices.doctor_id', $doctorId); // Use invoice doctor owner for payments

        if ($start)
            $paidQ->whereDate('payments.created_at', '>=', $start);
        if ($end)
            $paidQ->whereDate('payments.created_at', '<=', $end);

        $totalCollected = $paidQ->sum('payments.amount');
        $commissionEarned = $totalCollected * $rate;

        // C. Withdrawals
        $withdrawalsQ = \App\Models\Withdrawal::where('user_id', $doctorId);
        if ($start)
            $withdrawalsQ->whereDate('withdrawals.created_at', '>=', $start);
        if ($end)
            $withdrawalsQ->whereDate('withdrawals.created_at', '<=', $end);
        $totalWithdrawn = $withdrawalsQ->sum('amount');

        $balance = $commissionEarned - $totalWithdrawn;

        // allow negative balance (overdraft)
        return response()->json([
            'doctor' => $doctor->name,
            'summary' => [
                'total_invoiced' => $totalInvoiced,
                'commission_accrued' => $commissionInvoiced,
                'total_collected' => $totalCollected,
                'commission_earned' => $commissionEarned,
                'total_withdrawn' => $totalWithdrawn,
                'balance_due' => $balance
            ]
        ]);
    }

    // Export doctor summary CSV
    public function exportDoctor(Request $request, $doctorId)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $doctor = \App\Models\User::findOrFail($doctorId);
        $rate = ($doctor->commission_pct ?? 0) / 100;

        $invoicedQ = Invoice::query()
            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.doctor_id', $doctorId);
        if ($from)
            $invoicedQ->whereDate('invoices.created_at', '>=', $from);
        if ($to)
            $invoicedQ->whereDate('invoices.created_at', '<=', $to);
        $totalInvoiced = $invoicedQ->sum('invoice_items.subtotal');

        $paidQ = Payment::query()
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->where('invoices.doctor_id', $doctorId);
        if ($from)
            $paidQ->whereDate('payments.created_at', '>=', $from);
        if ($to)
            $paidQ->whereDate('payments.created_at', '<=', $to);
        $totalCollected = $paidQ->sum('payments.amount');
        $commissionEarned = $totalCollected * $rate;

        $withdrawalsQ = \App\Models\Withdrawal::where('user_id', $doctorId);
        if ($from)
            $withdrawalsQ->whereDate('withdrawals.created_at', '>=', $from);
        if ($to)
            $withdrawalsQ->whereDate('withdrawals.created_at', '<=', $to);
        $totalWithdrawn = $withdrawalsQ->sum('amount');

        // Audit export
        try {
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'export:doctor',
                'route' => "api/reports/doctor/{$doctorId}/export",
                'payload' => json_encode(['doctor_id' => $doctorId, 'from' => $from ?? null, 'to' => $to ?? null]),
                'ip' => $request->ip()
            ]);
        } catch (\Throwable $e) {
        }

        // Fallback: CSV
        $filename = 'doctor_' . $doctorId . '_summary_' . now()->format('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        $callback = function () use ($doctor, $totalInvoiced, $totalCollected, $commissionEarned, $totalWithdrawn) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['doctor', 'total_invoiced', 'total_collected', 'commission_earned', 'total_withdrawn', 'balance']);
            $balance = $commissionEarned - $totalWithdrawn;
            fputcsv($handle, [$doctor->name, $totalInvoiced, $totalCollected, $commissionEarned, $totalWithdrawn, $balance]);
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }

    // Export income as CSV/Excel (integration-ready with maatwebsite/excel)
    public function exportIncome(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $invoices = Invoice::when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->with('patient')
            ->get();

        // Fallback: generate CSV stream
        $filename = 'income-' . now()->format('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($invoices) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['invoice_number', 'patient', 'total', 'status', 'created_at']);
            foreach ($invoices as $inv) {
                fputcsv($handle, [$inv->invoice_number, $inv->patient->first_name ?? '-', $inv->total, $inv->status, $inv->created_at]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Export income to PDF (integration-ready) - uses dompdf/barryvdh if installed
    public function exportIncomePdf(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $invoices = Invoice::when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->with('patient')
            ->get();

        // Fallback: return simple HTML table with PDF headers
        $html = view('reports.income_pdf', compact('invoices'))->render();
        return response($html, 200, ['Content-Type' => 'application/pdf']);
    }

    // Export financial summary as CSV
    public function exportFinancials(Request $request)
    {
        $data = $this->financialSummary($request)->getData(true);

        $filename = 'financials_' . now()->format('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        // If maatwebsite/excel available, return XLSX
        // Audit export
        try {
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'export:financials',
                'route' => 'api/reports/export/financials',
                'payload' => json_encode($request->query()),
                'ip' => $request->ip()
            ]);
        } catch (\Throwable $e) {
        }

        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $rows = [];
            $rows[] = ['income', $data['income'] ?? 0];
            foreach (($data['expenses'] ?? []) as $k => $v) {
                if (is_array($v))
                    continue;
                $rows[] = ["expense_{$k}", $v];
            }
            $rows[] = ['net_profit', $data['net_profit'] ?? 0];
            $export = new \App\Exports\FinancialsExport($rows);
            return \Maatwebsite\Excel\Facades\Excel::download($export, 'financials.xlsx');
        }

        // Fallback: CSV stream
        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['item', 'value']);
            fputcsv($handle, ['income', $data['income'] ?? 0]);
            foreach (($data['expenses'] ?? []) as $k => $v) {
                if (is_array($v))
                    continue;
                fputcsv($handle, ["expense_{$k}", $v]);
            }
            fputcsv($handle, ['net_profit', $data['net_profit'] ?? 0]);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Render printable financial summary (HTML). If Dompdf installed, this can return a PDF download.
    public function exportFinancialsPdf(Request $request)
    {
        $data = $this->financialSummary($request)->getData(true);
        // If DomPDF is available, return a PDF download
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.financials_print', ['data' => $data]);
            try {
                \App\Models\AuditLog::create(['user_id' => auth()->id(), 'action' => 'export:financials:pdf', 'route' => 'api/reports/export/financials-pdf', 'payload' => json_encode($request->query()), 'ip' => $request->ip()]);
            } catch (\Throwable $e) {
            }
            return $pdf->download('financials_' . now()->format('YmdHis') . '.pdf');
        }

        $html = view('reports.financials_print', compact('data'))->render();
        return response($html, 200, ['Content-Type' => 'text/html']);
    }
}
