<?php

namespace App\Http\Requests\Api\ExamThreshold;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamThresholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pass_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'must_pass_every_subject' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'pass_score.required' => 'Pass score is required',
            'pass_score.numeric' => 'Pass score must be a number',
            'pass_score.min' => 'Pass score must be at least 0',
            'pass_score.max' => 'Pass score must not exceed 100',
            'must_pass_every_subject.boolean' => 'Must pass every subject must be true or false',
        ];
    }
}
