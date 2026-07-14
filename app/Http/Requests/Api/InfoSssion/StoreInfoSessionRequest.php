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
            'session_time'        => 'required|date_format:H:i',
            'expected_attendance' => 'required|integer|min:1',
            'attendance_count'    => 'nullable|integer|min:0',
            'hosts'               => 'required|array|min:1',
            'hosts.*.host_name'   => 'required|string|max:150',
        ];
    }
}
