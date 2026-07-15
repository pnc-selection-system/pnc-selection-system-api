<?php

namespace App\Http\Requests\Api\NgoPartner;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactPersonRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:30',
            'role' => 'nullable|string|max:100',
        ];
    }
}
