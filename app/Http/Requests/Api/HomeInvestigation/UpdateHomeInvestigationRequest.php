<?php

namespace App\Http\Requests\Api\HomeInvestigation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomeInvestigationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'candidate_id' => ['sometimes', 'exists:candidates,id'],
            'campaign_id' => ['nullable', 'exists:selection_campaigns,id'],
            'investigator_id' => ['nullable', 'exists:users,id'],
            'visit_date' => ['sometimes', 'date'],
            'location' => ['sometimes', 'string', 'max:255'],
            'people_met' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'findings' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['Assigned', 'In Progress', 'Submitted', 'Reviewed'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'candidate_id.exists' => 'The selected candidate is invalid.',
            'visit_date.date' => 'The visit date must be a valid date.',
            'location.max' => 'The location must not exceed 255 characters.',
            'status.in' => 'The status must be one of: Assigned, In Progress, Submitted, Reviewed.',
        ];
    }
}