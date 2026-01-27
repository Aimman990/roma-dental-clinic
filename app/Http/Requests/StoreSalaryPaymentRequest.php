<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'salary_sheet_id' => 'required|exists:salary_sheets,id',
            'user_id' => 'required|exists:users,id',
            'base_amount' => 'required|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0'
        ];
    }
}
