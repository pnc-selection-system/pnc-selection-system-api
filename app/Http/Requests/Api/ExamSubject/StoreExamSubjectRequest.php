<?php

namespace App\Http\Requests\Api\ExamSubject;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id'    => ['required', 'integer', 'exists:selection_campaigns,id'],
            'name'           => ['required', 'string', 'max:100'],
            'max_score'      => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'weight'         => ['nullable', 'numeric', 'min:0', 'max:99.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'campaign_id.required' => 'The campaign is required.',
            'campaign_id.exists'   => 'The selected campaign does not exist.',
            'name.required'        => 'The subject name is required.',
            'name.max'             => 'The subject name must not exceed 100 characters.',
            'max_score.required'   => 'The maximum score is required.',
            'max_score.numeric'    => 'The maximum score must be a number.',
            'max_score.min'        => 'The maximum score must be at least 0.01.',
        ];
    }
}
