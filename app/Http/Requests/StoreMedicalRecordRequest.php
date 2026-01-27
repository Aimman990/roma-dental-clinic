<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
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
            'doctor_id' => 'sometimes|nullable|exists:users,id',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'teeth' => 'nullable|array',
            'prescription' => 'nullable|string',
            'xray_path' => 'nullable|string'
        ];
    }
}
