<?php

namespace App\Http\Requests\Api\HomeInvestigation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHomeInvestigationRequest extends FormRequest
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
        $isAdmin = $this->user() && $this->user()->role_id === 1; // Admin role_id is 1

        return [
            'candidate_id' => ['required', 'exists:candidates,id'],
            'campaign_id' => ['nullable', 'exists:selection_campaigns,id'],
            'investigator_id' => $isAdmin ? ['nullable', 'exists:users,id'] : ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'people_met' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'findings' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Assigned', 'In Progress', 'Submitted', 'Reviewed'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'candidate_id.required' => 'The candidate is required.',
            'candidate_id.exists' => 'The selected candidate is invalid.',
            'visit_date.required' => 'The visit date is required.',
            'visit_date.date' => 'The visit date must be a valid date.',
            'location.required' => 'The location is required.',
            'location.max' => 'The location must not exceed 255 characters.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be one of: Assigned, In Progress, Submitted, Reviewed.',
        ];
    }
}