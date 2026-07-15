<?php

namespace App\Http\Requests\Api\InfoSssion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInfoSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id'         => 'sometimes|integer|exists:selection_campaigns,id',
            'village_id'          => 'sometimes|integer|exists:villages,id',
            'school_name'         => 'sometimes|string|max:150',
            'session_date'        => 'sometimes|date',
            'session_time'        => ['sometimes', 'regex:/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/'],
            'expected_attendance' => 'sometimes|integer|min:1',
            'attendance_count'    => 'nullable|integer|min:0',
            'hosts'               => 'sometimes|array|min:1',
            'hosts.*.host_name'   => 'required_with:hosts|string|max:150',
        ];
    }
}
