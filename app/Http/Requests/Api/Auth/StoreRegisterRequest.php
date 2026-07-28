<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:150',
            'email'    => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:30',
            'role_id'  => 'required|exists:roles,id',
        ];
    }

    /**
     * Allow frontend to send 'role' (string name) and resolve to role_id.
     */
    protected function prepareForValidation(): void
    {
        // Convert role name to role_id if role_id is not provided
        if ($this->filled('role') && ! $this->has('role_id')) {
            $role = \App\Models\Role::where('name', $this->role)->first();
            if ($role) {
                $this->merge(['role_id' => $role->id]);
            }
        }
    }
}
