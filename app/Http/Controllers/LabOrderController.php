<?php

namespace App\Http\Controllers;

use App\Models\LabOrder;
use App\Models\Patient;
use Illuminate\Http\Request;

class LabOrderController extends Controller
{
    public function index()
    {
        $orders = LabOrder::with(['patient', 'doctor'])->orderBy('due_date', 'asc')->get();
        return view('lab_orders.index', compact('orders'));
    }

    public function create()
    {
        $patients = Patient::all();
        return view('lab_orders.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'lab_name' => 'required|string|max:255',
            'work_type' => 'required|string|max:255',
            'details' => 'nullable|string',
            'sent_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:sent_date',
            'cost' => 'nullable|numeric|min:0',
        ]);

        LabOrder::create([
            ...$validated,
            'doctor_id' => auth()->id(),
            'status' => 'sent',
        ]);

        return redirect()->route('lab-orders.index')->with('success', 'تم إنشاء طلب المعمل');
    }

    public function show(LabOrder $labOrder)
    {
        // Simple show or reuse edit
        return view('lab_orders.show', compact('labOrder'));
    }

    public function edit(LabOrder $labOrder)
    {
        $patients = Patient::all();
        return view('lab_orders.edit', compact('labOrder', 'patients'));
    }

    public function update(Request $request, LabOrder $labOrder)
    {
        $validated = $request->validate([
            'lab_name' => 'required|string',
            'work_type' => 'required|string',
            'details' => 'nullable|string',
            'due_date' => 'nullable|date',
            'received_date' => 'nullable|date',
            'status' => 'required|in:sent,received,delivered',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $labOrder->update($validated);

        return redirect()->route('lab-orders.index')->with('success', 'تم تحديث الطلب');
    }

    public function destroy(LabOrder $labOrder)
    {
        $labOrder->delete();
        return redirect()->route('lab-orders.index')->with('success', 'تم حذف الطلب');
    }
}
