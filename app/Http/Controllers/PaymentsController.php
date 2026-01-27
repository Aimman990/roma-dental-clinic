<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;

class PaymentsController extends Controller
{
    public function __construct()
    {
        // payments are financial — admin only
        $this->middleware(function($request, $next){
            if (! auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Only administrators may access payments');
            }
            return $next($request);
        });
    }
    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();

        $invoice = Invoice::findOrFail($data['invoice_id']);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'patient_id' => $data['patient_id'] ?? $invoice->patient_id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'received_by' => auth()->id() ?? null,
            // generate a simple receipt number for quick references
            'receipt_number' => 'RCPT-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
        ]);

        // update invoice status and remaining (recalculate after creating payment)
        $totalPaid = $invoice->payments()->sum('amount');
        $remaining = max(0, $invoice->total - $totalPaid);
        $invoice->update([
            'remaining' => $remaining,
            'payment_method' => $data['method'] ?? $invoice->payment_method,
        ]);

        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial']);
        }

        return response()->json($payment, 201);
    }

    public function index()
    {
        return response()->json(Payment::with('invoice','patient')->paginate(30));
    }

    public function show(Payment $payment)
    {
        return response()->json($payment->load('invoice','patient'));
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();

        // recalculate invoice payment status and remaining after deletion
        $totalPaid = $invoice->payments()->sum('amount');
        $remaining = max(0, $invoice->total - $totalPaid);
        $invoice->update(['remaining' => $remaining]);
        if ($totalPaid <= 0) {
            $invoice->update(['status' => 'unpaid']);
        } elseif ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        return response()->json(['message' => 'deleted']);
    }
}
