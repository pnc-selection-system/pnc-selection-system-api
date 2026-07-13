<?php

namespace App\Http\Requests\InfoSession;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInfoSessionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'campaign_id'       => 'sometimes|exists:selection_campaigns,id',
            'partner_type'      => 'nullable|in:ngo,pnc',
            'ngo_name'          => 'required_if:partner_type,ngo|nullable|string|max:150',
            'ngo_contact'       => 'nullable|string|max:150',
            'pnc_department_id' => 'required_if:partner_type,pnc|nullable|string|max:100',
            'officer_id'        => 'required_if:partner_type,pnc|nullable|string|max:50',
            'date'              => 'sometimes|date_format:Y-m-d',
            'time'              => 'sometimes|date_format:H:i',
            'status'            => 'sometimes|in:upcoming,completed,cancelled',
            'province_id'       => 'sometimes|exists:provinces,id',
            'district_id'       => 'sometimes|exists:districts,id',
            'commune_id'        => 'sometimes|exists:communes,id',
            'village_id'        => 'sometimes|exists:villages,id',
            'school_id'         => 'sometimes|exists:schools,id',
            'hosted_by'         => 'nullable|string|max:150',
            'total_attendees'   => 'sometimes|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'ngo_name.required_if'          => 'Required when partner type is NGO',
            'pnc_department_id.required_if' => 'Required when partner type is PNC',
            'officer_id.required_if'        => 'Required when partner type is PNC',
        ];
    }
}
