<?php

namespace App\Http\Requests\Api\NgoPartner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNgoPartnerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:150',
            'type' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:65535',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'sometimes|boolean',
            'status' => 'nullable|string|max:50',
        ];
    }
}
