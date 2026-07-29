<?php

namespace App\Http\Requests\Api\NgoPartner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactPersonRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:100',
            'phone' => 'sometimes|required|string|max:30',
            'role' => 'nullable|string|max:100',
        ];
    }
}
