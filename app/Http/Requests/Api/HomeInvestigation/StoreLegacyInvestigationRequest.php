<?php

namespace App\Http\Requests\Api\HomeInvestigation;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegacyInvestigationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidateName' => ['required', 'string', 'max:255'],
            'campaign' => ['required', 'string', 'max:255'],
            'scheduledDate' => ['nullable', 'date'],
            'investigatorId' => ['nullable', 'exists:users,id'],
            'investigatorName' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'candidateName.required' => 'The candidate name is required.',
            'campaign.required' => 'The campaign is required.',
            'investigatorId.exists' => 'The selected investigator is invalid.',
        ];
    }
}
