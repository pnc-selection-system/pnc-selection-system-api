<?php

namespace App\Http\Requests\Api\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'province_id' => 'sometimes|required|integer|exists:provinces,id',
            'name' => 'sometimes|required|string|max:150',
            'address' => 'nullable|string',
        ];
    }
}
