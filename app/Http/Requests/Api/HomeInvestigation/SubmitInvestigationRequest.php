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
            'familySize' => ['required', 'integer', 'min:1', 'max:50'],
            'monthlyIncome' => ['required', 'numeric', 'min:0'],
            'occupation' => ['required', 'string', 'max:255'],
            'housingType' => ['required', 'string', 'in:Owned,Rented,Living with family,Provided by employer,Other'],
            'disability' => ['required', 'string', 'in:None,Physical,Visual,Hearing,Speech,Intellectual,Multiple,Other'],
            'educationLevel' => ['required', 'string', 'max:255'],
            'schoolName' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'in:Science,Society'],
            'graduationYear' => ['required', 'string', 'size:4'],
            'ranking' => ['required', 'string', 'in:A,B,C,D,E,F'],
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
            'familySize.required' => 'Please enter the family size.',
            'familySize.integer' => 'Family size must be a whole number.',
            'familySize.min' => 'Family size must be at least 1.',
            'familySize.max' => 'Family size must not exceed 50.',
            'monthlyIncome.required' => 'Please enter the monthly income.',
            'monthlyIncome.numeric' => 'Monthly income must be a valid number.',
            'monthlyIncome.min' => 'Monthly income cannot be negative.',
            'occupation.required' => 'Please enter the occupation.',
            'housingType.required' => 'Please select a housing type.',
            'housingType.in' => 'Please select a valid housing type.',
            'disability.required' => 'Please select a disability status.',
            'disability.in' => 'Please select a valid disability status.',
            'educationLevel.required' => 'Please select the education level.',
            'schoolName.required' => 'Please enter the school name.',
            'major.required' => 'Please select a major.',
            'major.in' => 'The major must be either Science or Society.',
            'graduationYear.required' => 'Please enter the graduation year.',
            'graduationYear.size' => 'The graduation year must be 4 digits (e.g., 2025).',
            'ranking.required' => 'Please select a ranking.',
            'ranking.in' => 'The ranking must be one of: A, B, C, D, E, F.',
        ];
    }
}
