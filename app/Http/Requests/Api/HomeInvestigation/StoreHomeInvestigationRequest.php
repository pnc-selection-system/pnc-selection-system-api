<?php

namespace App\Http\Requests\Api\HomeInvestigation;

use App\Models\Candidate;
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
        return [
            'candidate_id' => ['required', 'exists:candidates,id'],
            'campaign_id' => ['nullable', 'exists:selection_campaigns,id'],
            'investigator_id' => ['nullable', 'exists:users,id'],
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
            'candidate_id.valid_ngo' => 'The selected candidate has an invalid NGO reference.',
            'visit_date.required' => 'The visit date is required.',
            'visit_date.date' => 'The visit date must be a valid date.',
            'location.required' => 'The location is required.',
            'location.max' => 'The location must not exceed 255 characters.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be one of: Assigned, In Progress, Submitted, Reviewed.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $candidate = Candidate::with('ngoPartner')->find($this->candidate_id);

            if ($candidate && $candidate->ngo_id && ! $candidate->ngoPartner) {
                $validator->errors()->add(
                    'candidate_id',
                    'The selected candidate has an invalid NGO reference.'
                );
            }
        });
    }
}