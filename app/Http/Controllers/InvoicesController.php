<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Str;

class InvoicesController extends Controller
{
    public function __construct()
    {
        // invoices are financial — admin only
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Only administrators may access invoices');
            }
            return $next($request);
        });
    }
    public function index()
    {
        $query = Invoice::with('patient', 'items', 'payments', 'doctor');

        // Filters
        if ($df = request('date_from')) {
            $query->whereDate('created_at', '>=', $df);
        }
        if ($dt = request('date_to')) {
            $query->whereDate('created_at', '<=', $dt);
        }
        if ($doctor = request('doctor_id')) {
            $query->where('doctor_id', $doctor);
        }
        if ($status = request('status')) {
            $query->where('status', $status);
        }

        // Sorting options
        switch (request('sort')) {
            case 'oldest':
                $query->oldest('created_at');
                break;
            case 'amount_asc':
                $query->orderBy('total', 'asc');
                break;
            case 'amount_desc':
                $query->orderBy('total', 'desc');
                break;
            default:
                $query->latest('created_at');
        }

        // Compute totals based on the filtered set
        $totalInvoiced = (clone $query)->sum('total');
        $invoiceIds = (clone $query)->pluck('id')->toArray();
        $totalPaid = 0;
        if (!empty($invoiceIds)) {
            $totalPaid = \App\Models\Payment::whereIn('invoice_id', $invoiceIds)->sum('amount');
        }
        $totalDue = max(0, $totalInvoiced - $totalPaid);

        $invoices = $query->paginate(20)->withQueryString();

        // Return JSON for API requests with totals
        if (request()->wantsJson() || request()->ajax() || request()->is('api/*')) {
            return response()->json([
                'invoices' => $invoices,
                'totals' => [
                    'total_invoiced' => $totalInvoiced,
                    'total_paid' => $totalPaid,
                    'total_due' => $totalDue,
                ]
            ]);
        }

        $doctors = \App\Models\User::where('role', 'doctor')->select('id', 'name')->get();
        return view('invoices.index', compact('invoices', 'doctors', 'totalInvoiced', 'totalPaid', 'totalDue'));
    }

    public function create()
    {
        $patients = \App\Models\Patient::select('id', 'first_name', 'last_name', 'phone')->get();
        $doctors = \App\Models\User::where('role', 'doctor')->select('id', 'name', 'email')->get();
        return view('invoices.create', compact('patients', 'doctors'));
    }

    public function edit(Invoice $invoice)
    {
        $doctors = \App\Models\User::where('role', 'doctor')->select('id', 'name', 'email')->get();
        return view('invoices.edit', compact('invoice', 'doctors'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();

        // generate a simple invoice number
        $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

        $subtotal = 0;
        foreach ($data['items'] as $it) {
            $subtotal += $it['quantity'] * $it['unit_price'];
        }

        $discount = $data['discount'] ?? 0;
        $tax = $data['tax'] ?? 0;
        $total = max(0, $subtotal - $discount + $tax);

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'patient_id' => $data['patient_id'],
            'appointment_id' => $data['appointment_id'] ?? null,
            'doctor_id' => $data['doctor_id'] ?? null,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            // allow override of computed total
            'total' => $data['total'] ?? $total,
            'status' => 'unpaid'
        ]);

        foreach ($data['items'] as $it) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $it['service_id'] ?? null,
                'description' => $it['description'] ?? null,
                'quantity' => $it['quantity'],
                'unit_price' => $it['unit_price'],
                'subtotal' => $it['quantity'] * $it['unit_price']
            ]);
        }

        // optionally create an initial payment when specified
        if (!empty($data['initial_payment']) && $data['initial_payment'] > 0) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'amount' => $data['initial_payment'],
                'method' => $data['initial_payment_method'] ?? 'cash',
                'reference' => $data['initial_payment_reference'] ?? null,
                'received_by' => auth()->id() ?? null,
                'receipt_number' => 'RCPT-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
            ]);

            // update status based on initial payment
            $totalPaid = $invoice->payments()->sum('amount');
            if ($totalPaid >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $invoice->update(['status' => 'partial']);
            }
            // persist invoice-level payment info
            $remaining = max(0, $invoice->total - $totalPaid);
            $invoice->update(['remaining' => $remaining, 'payment_method' => $data['initial_payment_method'] ?? 'cash']);
        }

        // ensure remaining is set even when no initial payment provided
        if (!isset($invoice->remaining)) {
            $invoice->update(['remaining' => $invoice->total]);
        }

        return response()->json($invoice->load('patient', 'items', 'payments', 'doctor'), 201);
    }

    public function show(Invoice $invoice)
    {
        if (request()->wantsJson()) {
            return response()->json($invoice->load('patient', 'items', 'payments', 'adjustments', 'doctor'));
        }
        $invoice->load('patient', 'items', 'doctor', 'payments');
        return view('invoices.show', compact('invoice'));
    }

    public function update(\Illuminate\Http\Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'patient_id' => 'sometimes|exists:patients,id',
            'appointment_id' => 'sometimes|nullable|exists:appointments,id',
            'items' => 'sometimes|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'required_without:items.*.service_id|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'doctor_id' => 'nullable|exists:users,id',
            'total' => 'nullable|numeric|min:0'
        ]);

        // recalc subtotal if items provided
        if (!empty($data['items'])) {
            $subtotal = 0;
            // remove existing items then re-create
            $invoice->items()->delete();
            foreach ($data['items'] as $it) {
                $subtotal += $it['quantity'] * $it['unit_price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $it['service_id'] ?? null,
                    'description' => $it['description'] ?? null,
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'subtotal' => $it['quantity'] * $it['unit_price']
                ]);
            }
        } else {
            $subtotal = $invoice->subtotal;
        }

        $discount = $data['discount'] ?? $invoice->discount;
        $tax = $data['tax'] ?? $invoice->tax;
        $total = $data['total'] ?? max(0, $subtotal - $discount + $tax);

        $invoice->update([
            'doctor_id' => $data['doctor_id'] ?? $invoice->doctor_id,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
        ]);

        // ensure status aligns with payments and update remaining
        $paid = $invoice->payments()->sum('amount');
        if ($paid <= 0) {
            $invoice->update(['status' => 'unpaid']);
        } elseif ($paid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        // update remaining after any update
        $remaining = max(0, $invoice->total - $invoice->payments()->sum('amount'));
        $invoice->update(['remaining' => $remaining]);

        return response()->json($invoice->load('patient', 'items', 'payments', 'doctor'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة بنجاح');
    }

    // additional actions like update/cancel can be added
}
