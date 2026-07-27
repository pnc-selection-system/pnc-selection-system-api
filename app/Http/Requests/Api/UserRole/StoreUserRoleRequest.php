<?php

namespace App\Http\Requests\Api\UserRole;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'role_id'  => 'required|integer|exists:roles,id',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'nullable|string|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Allow frontend to send 'role' (string name) and resolve to role_id
        if ($this->filled('role') && ! $this->has('role_id')) {
            $role = \App\Models\Role::where('name', $this->role)->first();
            if ($role) {
                $this->merge(['role_id' => $role->id]);
            }
        }
    }
}
