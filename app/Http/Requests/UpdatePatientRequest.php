<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'national_id' => 'sometimes|nullable|string|max:100',
            'phone' => 'sometimes|nullable|string|max:50',
            'email' => 'sometimes|nullable|email|max:255',
            'birthdate' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|in:male,female',
            'notes' => 'sometimes|nullable|string'
        ];
    }
}
