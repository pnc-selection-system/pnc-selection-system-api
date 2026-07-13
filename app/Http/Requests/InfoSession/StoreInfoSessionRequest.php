<?php

namespace App\Http\Requests\InfoSession;

use Illuminate\Foundation\Http\FormRequest;

class StoreInfoSessionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'campaign_id'       => 'required|exists:selection_campaigns,id',
            'partner_type'      => 'nullable|in:ngo,pnc',
            'ngo_name'          => 'required_if:partner_type,ngo|nullable|string|max:150',
            'ngo_contact'       => 'nullable|string|max:150',
            'pnc_department_id' => 'required_if:partner_type,pnc|nullable|string|max:100',
            'officer_id'        => 'required_if:partner_type,pnc|nullable|string|max:50',
            'date'              => 'required|date_format:Y-m-d',
            'time'              => 'required|date_format:H:i',
            'status'            => 'sometimes|in:upcoming,completed,cancelled',
            'province_id'       => 'required|exists:provinces,id',
            'district_id'       => 'required|exists:districts,id',
            'commune_id'        => 'required|exists:communes,id',
            'village_id'        => 'required|exists:villages,id',
            'school_id'         => 'required|exists:schools,id',
            'hosted_by'         => 'nullable|string|max:150',
            'total_attendees'   => 'sometimes|integer|min:0',
            'interested_students'           => 'sometimes|array',
            'interested_students.*.name'    => 'required|string|max:150',
            'interested_students.*.contact' => 'nullable|string|max:100',
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
