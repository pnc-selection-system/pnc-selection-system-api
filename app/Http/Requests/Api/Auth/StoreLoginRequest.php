<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'    => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
        ];
    }
}
