<?php

namespace App\Http\Requests\Api\NgoPartner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNgoPartnerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:100',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'active' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
