<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\LabOrder;
use Carbon\Carbon;

class OperationalReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Render the Reports Hub view which lists available reports and links
        return view('reports.hub');
    }

    public function getPatientsReport(Request $request)
    {
        $start = $request->query('from');
        $end = $request->query('to');
        $patientId = $request->query('patient_id'); // Optional filter

        if ($patientId) {
            // Detailed single patient report
            $patient = Patient::with(['appointments', 'invoices.items', 'treatmentPlans', 'labOrders'])->findOrFail($patientId);

            // Filter relations by date if needed (client-side filtering might be enough for single patient history, but server-side is safer for large datasets)
            // For now, return full history as requested "Sijil Kamel"

            $totalPaid = $patient->invoices->sum(fn($inv) => $inv->payments->sum('amount'));
            $totalDue = $patient->invoices->sum('remaining');

            return response()->json([
                'type' => 'single',
                'data' => $patient,
                'stats' => [
                    'total_visits' => $patient->appointments->count(),
                    'total_paid' => $totalPaid,
                    'total_due' => $totalDue,
                    'last_visit' => $patient->appointments->max('appointment_date')
                ]
            ]);
        }

        // General Patients Stats
        $query = Patient::query();
        if ($start)
            $query->whereDate('patients.created_at', '>=', $start);
        if ($end)
            $query->whereDate('patients.created_at', '<=', $end);

        $newPatients = $query->count();

        // Top patients by revenue (requires joining payments)
        $topPatients = \DB::table('payments')
            ->join('patients', 'payments.patient_id', '=', 'patients.id')
            ->when($start, fn($q) => $q->whereDate('payments.created_at', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('payments.created_at', '<=', $end))
            ->select('patients.id', 'patients.first_name', 'patients.last_name', \DB::raw('SUM(payments.amount) as total_paid'))
            ->groupBy('patients.id', 'patients.first_name', 'patients.last_name')
            ->orderByDesc('total_paid')
            ->limit(10)
            ->get();

        return response()->json([
            'type' => 'general',
            'new_patients_count' => $newPatients,
            'top_patients' => $topPatients
        ]);
    }

    public function getDoctorsReport(Request $request)
    {
        $start = $request->query('from');
        $end = $request->query('to');
        $doctorId = $request->query('doctor_id');

        // CASE 1: Detailed Report for Specific Doctor (or if specifically requested via some flag, but usually by ID)
        // However, the Payouts page might want ALL details.
        // Let's check how the caller uses it.
        // reports/index.blade.php calls it WITHOUT doctor_id -> expects { doctors: [...] }
        // reports/payouts.blade.php calls it WITH doctor_id (or filters).

        // Wait, reports/payouts.blade.php calls: /api/reports/ops/doctors?from=...&to=...
        // It DOES NOT pass a doctor_id by default unless selected.
        // AND it expects: this.reportData = data.details || [];

        // CONFLICT:
        // reports/index.blade.php (General Tab) expects { doctors: [ {name, appointments_count} ... ] }
        // reports/payouts.blade.php (Finance Page) expects { details: [ ... ], summary: ... }

        // We need a way to distinguish.
        // The "General" tab in comprehensive reports is just a summary of activity.
        // The "Payouts" page is a detailed financial report.

        // Let's add a 'mode' parameter or check headers, OR separate the endpoints.
        // Since we are editing the existing controller, let's use a query param 'mode=financial' or similar if we want to force financial details for all.
        // BUT `reports/payouts.blade.php` was just created by me and it relies on `api/reports/ops/doctors`.

        // Let's Support BOTH structures based on `view_mode` or similar, defaulting to 'summary' if appropriate.
        // OR simply merge them if possible, but the structures are different.

        // Better: Use `type` param.
        // If type=financial (or inferred from context), return financial details.
        // If type=general (default for hub), return simple list.

        // Looking at `reports/index.blade.php` again:
        // fetch(`/api/reports/ops/doctors${qs}`) ... then d => this.doctorsData = d
        // x-for="doc in doctorsData.doctors"

        // Looking at `reports/payouts.blade.php`:
        // fetch(`/api/reports/ops/doctors?${params.toString()}`) ... data.details || []

        // Strategy:
        // I will check if `view=financial` is passed. I will update `payouts.blade.php` to pass `view=financial`.
        // AND I will restore the default behavior for `index.blade.php`.

        $mode = $request->query('mode', 'general'); // 'general' or 'financial'

        if ($mode === 'financial' || $doctorId) {
            // ... Financial/Detailed Logic ...
            $query = User::where('role', 'doctor');
            if ($doctorId)
                $query->where('users.id', $doctorId);
            $doctors = $query->get();

            $details = [];
            $totalCommission = 0;
            $totalWithdrawals = 0;

            foreach ($doctors as $doc) {
                // Invoices / Commission
                $invoices = Invoice::with(['items', 'doctor'])
                    ->where('invoices.doctor_id', $doc->id)
                    ->when($start, fn($q) => $q->whereDate('invoices.created_at', '>=', $start))
                    ->when($end, fn($q) => $q->whereDate('invoices.created_at', '<=', $end))
                    ->get();

                foreach ($invoices as $inv) {
                    $amount = $inv->items->sum('subtotal');
                    $commission = $amount * ($doc->commission_pct / 100);
                    if ($commission > 0) {
                        $details[] = [
                            'id' => 'comm_' . $inv->id,
                            'date' => $inv->created_at->format('Y-m-d'),
                            'doctor_name' => $doc->name,
                            'type' => 'commission',
                            'details' => "عمولة فاتورة #{$inv->id} (مبلغ: {$amount})",
                            'amount' => $commission
                        ];
                        $totalCommission += $commission;
                    }
                }

                // Withdrawals
                $withdrawals = \App\Models\Withdrawal::where('user_id', $doc->id)
                    ->when($start, fn($q) => $q->whereDate('withdrawals.created_at', '>=', $start))
                    ->when($end, fn($q) => $q->whereDate('withdrawals.created_at', '<=', $end))
                    ->get();

                foreach ($withdrawals as $w) {
                    $details[] = [
                        'id' => 'with_' . $w->id,
                        'date' => $w->created_at->format('Y-m-d'),
                        'doctor_name' => $doc->name,
                        'type' => 'withdrawal',
                        'details' => $w->notes ?? 'سحب نقدي',
                        'amount' => $w->amount
                    ];
                    $totalWithdrawals += $w->amount;
                }
            }

            usort($details, fn($a, $b) => strcmp($b['date'], $a['date']));

            return response()->json([
                'summary' => [
                    'total_commission' => $totalCommission,
                    'total_withdrawals' => $totalWithdrawals,
                    'net_balance' => $totalCommission - $totalWithdrawals
                ],
                'details' => $details
            ]);
        }

        // CASE 2: General Summary (Appointment Counts) for Reports Hub
        $doctors = User::where('role', 'doctor')->get()->map(function ($doc) use ($start, $end) {
            $count = Appointment::where('doctor_id', $doc->id)
                ->when($start, fn($q) => $q->whereDate('appointment_date', '>=', $start))
                ->when($end, fn($q) => $q->whereDate('appointment_date', '<=', $end))
                ->count();
            return [
                'id' => $doc->id,
                'name' => $doc->name,
                'appointments_count' => $count
            ];
        });

        return response()->json([
            'type' => 'general',
            'doctors' => $doctors
        ]);
    }

    // Doctors summary (per-doctor aggregates) - admin only
    public function doctorsSummary(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators may access doctors reports');
        }

        $start = $request->query('from');
        $end = $request->query('to');
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 20);

        $base = \App\Models\User::where('role', 'doctor')
            ->when($q, fn($qb) => $qb->where('name', 'like', "%{$q}%"));

        $page = $request->query('page', 1);
        $doctors = $base->forPage($page, $perPage)->get();
        $total = $base->count();

        $items = [];
        foreach ($doctors as $doc) {
            $rate = ($doc->commission_pct ?? 0) / 100;

            $apptsQ = \App\Models\Appointment::where('doctor_id', $doc->id);
            $invoicesItemsQ = \App\Models\Invoice::join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->where('invoices.doctor_id', $doc->id);
            $paymentsQ = \App\Models\Payment::join('invoices', 'payments.invoice_id', '=', 'invoices.id')
                ->where('invoices.doctor_id', $doc->id);
            $withdrawQ = \App\Models\Withdrawal::where('user_id', $doc->id);

            if ($start) {
                $apptsQ->whereDate('start_at', '>=', $start);
                $invoicesItemsQ->whereDate('invoices.created_at', '>=', $start);
                $paymentsQ->whereDate('payments.created_at', '>=', $start);
                $withdrawQ->whereDate('withdrawals.created_at', '>=', $start);
            }
            if ($end) {
                $apptsQ->whereDate('start_at', '<=', $end);
                $invoicesItemsQ->whereDate('invoices.created_at', '<=', $end);
                $paymentsQ->whereDate('payments.created_at', '<=', $end);
                $withdrawQ->whereDate('withdrawals.created_at', '<=', $end);
            }

            $appointments_count = $apptsQ->count();
            $invoices_count = \App\Models\Invoice::where('doctor_id', $doc->id)
                ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
                ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
                ->count();

            $total_invoiced = (float) $invoicesItemsQ->sum('invoice_items.subtotal');
            $total_collected = (float) $paymentsQ->sum('payments.amount');
            $commission_earned = $total_collected * $rate;
            $total_withdrawn = (float) $withdrawQ->sum('amount');
            $balance = $commission_earned - $total_withdrawn;

            $items[] = [
                'id' => $doc->id,
                'name' => $doc->name,
                'appointments_count' => $appointments_count,
                'invoices_count' => $invoices_count,
                'total_invoiced' => $total_invoiced,
                'total_collected' => $total_collected,
                'commission_earned' => $commission_earned,
                'total_withdrawn' => $total_withdrawn,
                'balance' => $balance,
                'commission_pct' => $doc->commission_pct ?? 0
            ];
        }

        return response()->json([
            'data' => $items,
            'meta' => [
                'total' => $total,
                'page' => (int) $page,
                'per_page' => (int) $perPage
            ]
        ]);
    }

    // Services summary (per-service aggregates)
    public function servicesSummary(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators may access services reports');
        }

        $start = $request->query('from');
        $end = $request->query('to');
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 20);

        $base = \App\Models\Service::query()
            ->when($q, fn($qb) => $qb->where('name', 'like', "%{$q}%"));

        $page = (int) $request->query('page', 1);
        $services = $base->forPage($page, $perPage)->get();
        $total = $base->count();

        $items = [];
        foreach ($services as $s) {
            $invQ = \App\Models\Invoice::join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->where('invoice_items.service_id', $s->id);
            if ($start)
                $invQ->whereDate('invoices.created_at', '>=', $start);
            if ($end)
                $invQ->whereDate('invoices.created_at', '<=', $end);

            $invoices_count = (int) $invQ->distinct('invoices.id')->count('invoices.id');
            $total_revenue = (float) $invQ->sum('invoice_items.subtotal');

            $items[] = [
                'id' => $s->id,
                'name' => $s->name,
                'price' => (float) $s->price,
                'invoices_count' => $invoices_count,
                'total_revenue' => $total_revenue
            ];
        }

        return response()->json(['data' => $items, 'meta' => ['total' => $total, 'page' => $page, 'per_page' => $perPage]]);
    }

    // Inventory summary
    public function inventorySummary(Request $request)
    {
        // Inventory features are currently DISABLED per user request (non-destructive).
        // Return an empty/disabled payload so front-end can handle gracefully.
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            // still protect the endpoint
            abort(403, 'Only administrators may access inventory reports');
        }

        return response()->json([
            'data' => [],
            'meta' => ['total' => 0, 'page' => 1, 'per_page' => 20],
            'summary' => ['total_items' => 0, 'total_value' => 0.0, 'low_stock_count' => 0, 'disabled' => true]
        ]);
    }

    // Export services as XLSX or CSV
    public function exportServices(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin')
            abort(403);

        $start = $request->query('from');
        $end = $request->query('to');
        $q = $request->query('q');

        $base = \App\Models\Service::query()->when($q, fn($qb) => $qb->where('name', 'like', "%{$q}%"));
        $services = $base->get();

        $rows = [];
        foreach ($services as $s) {
            $invQ = \App\Models\Invoice::join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->where('invoice_items.service_id', $s->id)
                ->when($start, fn($q) => $q->whereDate('invoices.created_at', '>=', $start))
                ->when($end, fn($q) => $q->whereDate('invoices.created_at', '<=', $end));

            $invoices_count = (int) $invQ->distinct('invoices.id')->count('invoices.id');
            $total_revenue = (float) $invQ->sum('invoice_items.subtotal');

            $rows[] = [$s->id, $s->name, (float) $s->price, $invoices_count, $total_revenue];
        }

        // Audit
        try {
            \App\Models\AuditLog::create(['user_id' => auth()->id(), 'action' => 'export:services', 'route' => 'api/reports/services/export', 'payload' => json_encode($request->query()), 'ip' => $request->ip()]);
        } catch (\Throwable $e) {
        }

        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $export = new \Maatwebsite\Excel\SheetCollection(['Services' => collect($rows)]);
            // Build a quick Export object using the rows via FromCollection wrapper
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DoctorsSummaryExport($rows), 'services_report.xlsx');
        }

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['id', 'name', 'price', 'invoices_count', 'total_revenue']);
            foreach ($rows as $r)
                fputcsv($out, $r);
            fclose($out);
        };
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="services_report.csv"'];
        return response()->stream($callback, 200, $headers);
    }

    // Export inventory as XLSX or CSV
    public function exportInventory(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin')
            abort(403);

        // Return a small CSV explaining the inventory export is disabled
        $callback = function () {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['message']);
            fputcsv($out, ['Inventory module is disabled by administrator']);
            fclose($out);
        };
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="inventory_disabled.csv"'];
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export doctors summary as CSV. If you later install maatwebsite/excel,
     * you can replace this with an XLSX exporter.
     */
    public function exportDoctors(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators may export doctors reports');
        }

        $start = $request->query('from');
        $end = $request->query('to');
        $q = $request->query('q');

        $base = \App\Models\User::where('role', 'doctor')
            ->when($q, fn($qb) => $qb->where('name', 'like', "%{$q}%"));

        $doctors = $base->get();

        // Build rows first
        $rows = [];
        foreach ($doctors as $doc) {
            $rate = ($doc->commission_pct ?? 0) / 100;

            $apptsQ = \App\Models\Appointment::where('doctor_id', $doc->id);
            $invoicesItemsQ = \App\Models\Invoice::join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->where('invoices.doctor_id', $doc->id);
            $paymentsQ = \App\Models\Payment::join('invoices', 'payments.invoice_id', '=', 'invoices.id')
                ->where('invoices.doctor_id', $doc->id);
            $withdrawQ = \App\Models\Withdrawal::where('user_id', $doc->id);

            if ($start) {
                $apptsQ->whereDate('start_at', '>=', $start);
                $invoicesItemsQ->whereDate('invoices.created_at', '>=', $start);
                $paymentsQ->whereDate('payments.created_at', '>=', $start);
                $withdrawQ->whereDate('withdrawals.created_at', '>=', $start);
            }
            if ($end) {
                $apptsQ->whereDate('start_at', '<=', $end);
                $invoicesItemsQ->whereDate('invoices.created_at', '<=', $end);
                $paymentsQ->whereDate('payments.created_at', '<=', $end);
                $withdrawQ->whereDate('withdrawals.created_at', '<=', $end);
            }

            $appointments_count = $apptsQ->count();
            $invoices_count = \App\Models\Invoice::where('doctor_id', $doc->id)
                ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
                ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
                ->count();

            $total_invoiced = (float) $invoicesItemsQ->sum('invoice_items.subtotal');
            $total_collected = (float) $paymentsQ->sum('payments.amount');
            $commission_earned = $total_collected * $rate;
            $total_withdrawn = (float) $withdrawQ->sum('amount');
            $balance = $commission_earned - $total_withdrawn;

            $rows[] = [
                $doc->id,
                $doc->name,
                $appointments_count,
                $invoices_count,
                number_format($total_invoiced, 2, '.', ''),
                number_format($total_collected, 2, '.', ''),
                number_format($commission_earned, 2, '.', ''),
                number_format($total_withdrawn, 2, '.', ''),
                number_format($balance, 2, '.', '')
            ];
        }

        // Record export in audit log
        try {
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'export:doctors',
                'route' => 'api/reports/doctors/export',
                'payload' => json_encode(['from' => $start, 'to' => $end]),
                'ip' => $request->ip()
            ]);
        } catch (\Throwable $e) {
            // don't block export on audit failure
        }

        // If Excel is available, return XLSX
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DoctorsSummaryExport($rows), 'doctors_report.xlsx');
        }

        // Fallback: CSV stream
        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM for Excel compatibility with UTF-8
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['id', 'name', 'appointments_count', 'invoices_count', 'total_invoiced', 'total_collected', 'commission_earned', 'total_withdrawn', 'balance']);
            foreach ($rows as $r)
                fputcsv($out, $r);
            fclose($out);
        };

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="doctors_report.csv"'
        ];

        return response()->stream($callback, 200, $headers);
    }

    public function getOperationsReport(Request $request)
    {
        $start = $request->query('from');
        $end = $request->query('to');

        // Appointments Status
        $apptStats = Appointment::select('status', \DB::raw('count(*) as total'))
            ->when($start, fn($q) => $q->whereDate('appointment_date', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('appointment_date', '<=', $end))
            ->groupBy('status')
            ->get();

        // Inventory: low stock alerts are disabled when inventory module is disabled
        // Return empty set and mark disabled to avoid running inventory queries
        $lowStock = collect([]);
        // $expiring = InventoryItem::whereDate('expiry_date', '<=', now()->addMonths(3))->get(); // if expiry_date exists

        // Lab Orders
        $labStats = LabOrder::select('status', \DB::raw('count(*) as total'))
            ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
            ->groupBy('status')
            ->get();

        return response()->json([
            'appointments_breakdown' => $apptStats,
            'lab_orders_breakdown' => $labStats,
            'inventory_alerts' => [
                'disabled' => true,
                'low_stock_count' => 0,
                'low_stock_items' => []
            ]
        ]);
    }

    public function getFinancialsReport(Request $request)
    {
        $start = $request->query('from');
        $end = $request->query('to');
        $type = $request->query('type', 'all'); // 'expenses', 'payouts', 'all'

        // Expenses List
        $expenses = \App\Models\Expense::with('recorder')
            ->when($start, fn($q) => $q->whereDate('incurred_on', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('incurred_on', '<=', $end))
            ->latest('incurred_on')
            ->get();

        // Withdrawals (Doctor Payouts)
        $withdrawals = \App\Models\Withdrawal::with('user')
            ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
            ->latest('created_at')
            ->get();

        // Lab Order Costs (Transactions)
        $labCosts = LabOrder::with(['patient', 'doctor'])
            ->where('cost', '>', 0)
            ->when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
            ->latest('created_at')
            ->get();

        return response()->json([
            'expenses' => $expenses,
            'withdrawals' => $withdrawals,
            'lab_costs' => $labCosts
        ]);
    }
}
