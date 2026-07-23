<?php

namespace App\Http\Requests\Api\HomeInvestigation;

use Illuminate\Foundation\Http\FormRequest;

class SubmitInvestigationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitDate' => ['required', 'date'],
            'location' => ['required', 'string', 'max:500'],
            'gpsCoordinates' => ['nullable', 'string', 'max:100'],
            'peopleMet' => ['required', 'string', 'min:1', 'max:1000'],
            'observations' => ['required', 'string', 'min:10'],
            'findings' => ['required', 'string', 'min:10'],
            'recommendation' => ['required', 'string', 'in:Recommend,Not Recommend'],
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'visitDate.required' => 'The visit date is required.',
            'visitDate.date' => 'The visit date must be a valid date.',
            'location.required' => 'The location is required.',
            'peopleMet.required' => 'Please specify who you met during the visit.',
            'observations.required' => 'The observations field is required.',
            'observations.min' => 'Observations must be at least 10 characters.',
            'findings.required' => 'The findings field is required.',
            'findings.min' => 'Findings must be at least 10 characters.',
            'recommendation.required' => 'Please provide a recommendation.',
            'recommendation.in' => 'The recommendation must be either "Recommend" or "Not Recommend".',
            'reason.required' => 'Please provide a reason for your recommendation.',
        ];
    }
}
