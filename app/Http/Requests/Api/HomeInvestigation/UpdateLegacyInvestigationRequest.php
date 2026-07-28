<?php

namespace App\Http\Requests\Api\HomeInvestigation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLegacyInvestigationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidateName' => ['sometimes', 'string', 'max:255'],
            'campaign' => ['sometimes', 'string', 'max:255'],
            'scheduledDate' => ['nullable', 'date'],
            'investigatorId' => ['nullable', 'exists:users,id'],
            'investigatorName' => ['nullable', 'string', 'max:255'],
            'visitDate' => ['nullable', 'date'],
            'recommendation' => ['nullable', 'string', 'in:Recommend,Not Recommend'],
            'notes' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],
        ];
    }
}
