<?php

namespace App\Http\Requests\Api\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id' => 'sometimes|required|integer|exists:selection_campaigns,id',
            'province_id' => 'sometimes|required|integer|exists:provinces,id',
            'school_id' => 'sometimes|required|integer|exists:schools,id',
            'ngo_id' => 'nullable|integer|exists:ngo_partners,id',
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'gender' => 'sometimes|required|string|in:Male,Female,Other',
            'dob' => 'sometimes|required|date',
            'phone' => 'sometimes|required|string|max:30',
            'email' => 'nullable|email|max:100',
            'national' => 'nullable|string|max:50',
            'photo' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'household_size' => 'nullable|integer',
            'father_name' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:100',
            'guardian_name' => 'nullable|string|max:100',
            'family_income' => 'nullable|numeric|min:0',
            'housing' => 'nullable|string|max:100',
            'graduation_year' => 'nullable|integer|digits:4',
            'current_grade' => 'nullable|string|max:50',
            'status' => 'sometimes|string|in:Pending,Approved,Rejected,Withdrawn,Held,Selected',
        ];
    }
}
