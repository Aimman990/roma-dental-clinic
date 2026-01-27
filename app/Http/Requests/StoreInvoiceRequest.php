<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'sometimes|nullable|exists:appointments,id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.description' => 'required_without:items.*.service_id|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'doctor_id' => 'nullable|exists:users,id',
            // allow overriding the computed total (optional)
            'total' => 'nullable|numeric|min:0',
            // optionally record an initial payment amount when creating the invoice
            'initial_payment' => 'nullable|numeric|min:0',
            'initial_payment_method' => 'nullable|string',
            'initial_payment_reference' => 'nullable|string',
        ];
    }
}
