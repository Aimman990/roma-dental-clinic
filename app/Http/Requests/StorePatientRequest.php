<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated staff who are part of clinic roles can create patients
        $u = $this->user();
        if (! $u) return false;

        // we simplified roles to only 'admin' and 'user' — allow both to create patients
        return in_array($u->role, ['admin','user']);
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'notes' => 'nullable|string'
        ];
    }
}
