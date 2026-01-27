<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'patient_id' => 'sometimes|nullable|exists:patients,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,bank_transfer,other',
            'reference' => 'nullable|string|max:255',
        ];
    }
}
