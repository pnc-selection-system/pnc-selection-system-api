<?php

namespace App\Http\Requests\Api\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'province_id' => 'required|integer|exists:provinces,id',
            'name' => 'required|string|max:150',
            'address' => 'nullable|string',
        ];
    }
}
