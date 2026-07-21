<?php

namespace App\Http\Requests\Api\InfoSssion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInfoSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'school'       => $this->school ?? $this->school_name,
            'partner_name' => $this->partner_name ?? $this->ngo_name,
        ]);
    }

    public function rules(): array
    {
        return [
            'campaign_id'         => 'sometimes|integer|exists:selection_campaigns,id',
            'province_id'         => 'nullable|integer|exists:provinces,id',
            'district_id'         => 'nullable|integer|exists:districts,id',
            'commune_id'          => 'nullable|integer|exists:communes,id',
            'village_id'          => 'sometimes|integer|exists:villages,id',
            'school'              => 'sometimes|string|max:150',
            'session_date'        => 'sometimes|date',
            'session_time'        => ['sometimes', 'regex:/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/'],
            'expected_attendance' => 'sometimes|integer|min:1',
            'attendance_count'    => 'nullable|integer|min:0',
            'hosts'               => 'sometimes|array|min:1',
            'hosts.*.host_name'   => 'required_with:hosts|string|max:150',
            'partner_type'        => 'nullable|string|in:NGO,Officer',
            'partner_name'        => 'nullable|string|max:150|required_if:partner_type,NGO',
            'host_by'             => 'nullable|string|max:150',
        ];
    }
}
