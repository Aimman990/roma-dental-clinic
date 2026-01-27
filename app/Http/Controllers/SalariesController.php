<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaryPaymentRequest;
use App\Http\Requests\StoreSalarySheetRequest;
use App\Models\SalaryPayment;
use App\Models\SalarySheet;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Withdrawal;
use App\Http\Requests\StoreWithdrawalRequest;
use Illuminate\Http\Request;

class SalariesController extends Controller
{
    public function __construct()
    {
        // payroll is financial — admin only
        $this->middleware(function($request, $next){
            if (! auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Only administrators may access payroll');
            }
            return $next($request);
        });
    }
    public function index(Request $request)
    {
        return response()->json(SalarySheet::with('payments.user')->paginate(20));
    }

    public function storeSheet(StoreSalarySheetRequest $request)
    {
        $sheet = SalarySheet::create(['period' => $request->validated()['period'], 'total' => 0]);
        return response()->json($sheet, 201);
    }

    public function addPayment(StoreSalaryPaymentRequest $request)
    {
        $data = $request->validated();
        $totalPaid = ($data['base_amount'] ?? 0) + ($data['commission'] ?? 0) - ($data['deductions'] ?? 0);

        $payment = SalaryPayment::create([
            'salary_sheet_id' => $data['salary_sheet_id'],
            'user_id' => $data['user_id'],
            'base_amount' => $data['base_amount'],
            'commission' => $data['commission'] ?? 0,
            'deductions' => $data['deductions'] ?? 0,
            'total_paid' => $totalPaid,
        ]);

        // update sheet total
        $sheet = SalarySheet::find($data['salary_sheet_id']);
        $sheet->total = $sheet->payments()->sum('total_paid');
        $sheet->save();

        return response()->json($payment, 201);
    }

    // generate salary sheet: compute each doctor's commission from services in period
    public function generate(Request $request)
    {
        $period = $request->query('period') ?? now()->format('Y-m');
        // interpret period as month start/end
        $start = $request->query('from') ?? $period . '-01';
        $end = $request->query('to') ?? date('Y-m-t', strtotime($start));

        // sum invoice_item.subtotal per doctor
        $rows = Invoice::join('invoice_items','invoices.id','invoice_items.invoice_id')
            ->join('services','invoice_items.service_id','services.id')
            ->whereBetween('invoices.created_at', [$start, $end])
            ->selectRaw('services.doctor_id as doctor_id, SUM(invoice_items.subtotal) as total_services')
            ->groupBy('services.doctor_id')
            ->get();

        $sheet = SalarySheet::create(['period' => $period, 'total' => 0]);

        foreach($rows as $r) {
            if (! $r->doctor_id) continue;
            $doctor = User::find($r->doctor_id);
            if (! $doctor) continue;
            $commissionPct = $doctor->commission_pct ?? 0;
            $commissionAmount = ($r->total_services * ($commissionPct / 100));

            SalaryPayment::create([
                'salary_sheet_id' => $sheet->id,
                'user_id' => $doctor->id,
                'base_amount' => 0,
                'commission' => $commissionAmount,
                'deductions' => 0,
                'total_paid' => $commissionAmount,
            ]);
        }

        // Add fixed monthly salaries for staff / non-doctors who have a monthly_salary set
        $staffQ = User::where('role', '!=', 'doctor')->where('monthly_salary', '>', 0)->get();
        foreach ($staffQ as $staff) {
            SalaryPayment::create([
                'salary_sheet_id' => $sheet->id,
                'user_id' => $staff->id,
                'base_amount' => $staff->monthly_salary,
                'commission' => 0,
                'deductions' => 0,
                'total_paid' => $staff->monthly_salary,
            ]);
        }

        // update sheet total
        $sheet->total = $sheet->payments()->sum('total_paid');
        $sheet->save();

        return response()->json($sheet->load('payments.user'));
    }

    public function withdraw(StoreWithdrawalRequest $request)
    {
        $data = $request->validated();
        $data['recorded_by'] = auth()->id() ?? null;
        $w = Withdrawal::create($data);
        return response()->json($w, 201);
    }

    // List withdrawals (optionally filtered by user_id)
    public function withdrawals(Request $request)
    {
        $q = Withdrawal::query();
        if ($request->has('user_id')) {
            $q->where('user_id', $request->query('user_id'));
        }
        if ($request->has('from')) {
            $q->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->has('to')) {
            $q->whereDate('created_at', '<=', $request->query('to'));
        }
        $list = $q->orderBy('created_at', 'desc')->get();
        return response()->json($list);
    }

    // Update a withdrawal
    public function updateWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|string',
            'note' => 'nullable|string',
            'date' => 'nullable|date'
        ]);
        $withdrawal->update([
            'amount' => $data['amount'],
            'method' => $data['method'] ?? $withdrawal->method,
            'note' => $data['note'] ?? $withdrawal->note,
        ]);
        if (!empty($data['date'])) {
            $withdrawal->created_at = $data['date'];
            $withdrawal->save();
        }
        return response()->json($withdrawal);
    }

    // Delete a withdrawal
    public function deleteWithdrawal(Withdrawal $withdrawal)
    {
        $withdrawal->delete();
        return response()->json(['message' => 'deleted']);
    }
}
