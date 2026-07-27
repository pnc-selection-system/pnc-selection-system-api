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
            'familySize' => ['nullable', 'integer', 'min:1', 'max:50'],
            'monthlyIncome' => ['nullable', 'numeric', 'min:0'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'housingType' => ['nullable', 'string', 'max:100'],
            'disability' => ['nullable', 'string', 'max:100'],
            'educationLevel' => ['nullable', 'string', 'max:255'],
            'schoolName' => ['nullable', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'graduationYear' => ['nullable', 'string', 'size:4'],
            'ranking' => ['nullable', 'string', 'in:A,B,C,D,E,F'],
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
