<?php

namespace App\Http\Requests\Api\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id'   => 'sometimes|required|integer|exists:selection_campaigns,id',
            'province_id'   => 'sometimes|required|integer|exists:provinces,id',
            'school_name'   => 'sometimes|required|string|max:200',
            'ngo_id'        => 'nullable|integer|exists:ngo_partners,id',
            'first_name'    => 'sometimes|required|string|max:100',
            'last_name'     => 'sometimes|required|string|max:100',
            'first_name_kh' => 'nullable|string|max:100',
            'last_name_kh'  => 'nullable|string|max:100',
            'gender'        => 'sometimes|required|string|in:Male,Female,Other',
            'dob'           => 'sometimes|required|date',
            'phone'         => 'sometimes|required|string|max:30',
            'status'        => 'sometimes|string',
        ];
    }
}
