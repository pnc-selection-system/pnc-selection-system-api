<?php

namespace App\Http\Requests\Api\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id' => 'required|integer|exists:selection_campaigns,id',
            'province_id' => 'required|integer|exists:provinces,id',
            'school_id' => 'required|integer|exists:schools,id',
            'ngo_id' => 'nullable|integer|exists:ngo_partners,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|string|in:Male,Female,Other',
            'dob' => 'required|date',
            'phone' => 'required|string|max:30',
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
