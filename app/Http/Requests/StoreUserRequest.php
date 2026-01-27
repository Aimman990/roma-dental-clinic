<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // only admins allowed via middleware, but double-check here
        $u = $this->user();
        return $u && $u->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|required_unless:role,doctor|email|unique:users,email',
            // password is required for admin and user but optional for doctor
            'password' => 'nullable|required_if:role,admin,user|string|min:6|confirmed',
            // simplified roles: admin, user, or doctor
            'role' => 'required|in:admin,user,doctor',
            'commission_pct' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
        ];
    }
}
