<?php

namespace App\Http\Requests\Api\NgoPartner;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunicationLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'channel' => 'required|string|max:50',
            'summary' => 'required|string|max:1000',
        ];
    }
}
