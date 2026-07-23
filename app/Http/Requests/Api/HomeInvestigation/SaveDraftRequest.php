<?php

namespace App\Http\Requests\Api\HomeInvestigation;

use Illuminate\Foundation\Http\FormRequest;

class SaveDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitDate' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:500'],
            'gpsCoordinates' => ['nullable', 'string', 'max:100'],
            'peopleMet' => ['nullable', 'string', 'max:1000'],
            'observations' => ['nullable', 'string'],
            'findings' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string', 'in:Recommend,Not Recommend'],
            'reason' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'visitDate.date' => 'The visit date must be a valid date.',
            'recommendation.in' => 'The recommendation must be either "Recommend" or "Not Recommend".',
        ];
    }
}
