<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'sometimes|required|exists:patients,id',
            'doctor_id' => 'sometimes|nullable|exists:users,id',
            'start_at' => 'sometimes|required|date',
            'end_at' => 'sometimes|nullable|date|after_or_equal:start_at',
            'status' => 'sometimes|nullable|in:scheduled,confirmed,cancelled,completed,no_show',
            'notes' => 'sometimes|nullable|string'
        ];
    }
}
