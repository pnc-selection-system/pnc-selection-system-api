<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:150',
            'email'    => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone'    => 'nullable|string|max:30',
            'role_id'  => 'required|exists:roles,id',
        ];
    }
}
