<?php

namespace App\Http\Requests\Api\InfoSssion;

use Illuminate\Foundation\Http\FormRequest;

class StoreInfoSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id'         => 'required|integer|exists:selection_campaigns,id',
            'village_id'          => 'required|integer|exists:villages,id',
            'school_name'         => 'required|string|max:150',
            'session_date'        => 'required|date',
            'session_time'        => ['sometimes', 'regex:/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/'],
            'expected_attendance' => 'required|integer|min:1',
            'attendance_count'    => 'nullable|integer|min:0',
            'hosts'               => 'required|array|min:1',
            'hosts.*.host_name'   => 'required|string|max:150',
            'partner_type'        => 'nullable|string|in:NGO,Officer',
            'ngo_name'            => 'nullable|string|max:150|required_if:partner_type,NGO',
        ];
    }
}
