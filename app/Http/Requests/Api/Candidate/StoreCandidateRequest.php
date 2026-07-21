<?php

namespace App\Http\Requests\Api\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id'   => 'required|integer|exists:selection_campaigns,id',
            'province_id'   => 'required|integer|exists:provinces,id',
            'school_name'   => 'required|string|max:200',
            'ngo_id'        => 'nullable|integer|exists:ngo_partners,id',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'first_name_kh' => 'nullable|string|max:100',
            'last_name_kh'  => 'nullable|string|max:100',
            'gender'        => 'required|string|in:Male,Female,Other',
            'dob'           => 'required|date',
            'phone'         => 'required|string|max:30',
            'status'        => 'sometimes|string',
        ];
    }
}
