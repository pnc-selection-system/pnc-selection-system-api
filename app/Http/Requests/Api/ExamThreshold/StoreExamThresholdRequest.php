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
            'overall_pass_mark' => ['required', 'numeric', 'min:0', 'max:100'],
            'per_subject_min' => ['required', 'numeric', 'min:0', 'max:100'],
            'must_pass_every_subject' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'overall_pass_mark.required' => 'Overall pass mark is required',
            'overall_pass_mark.numeric' => 'Overall pass mark must be a number',
            'overall_pass_mark.min' => 'Overall pass mark must be at least 0',
            'overall_pass_mark.max' => 'Overall pass mark must not exceed 100',
            'per_subject_min.required' => 'Per subject minimum is required',
            'per_subject_min.numeric' => 'Per subject minimum must be a number',
            'per_subject_min.min' => 'Per subject minimum must be at least 0',
            'per_subject_min.max' => 'Per subject minimum must not exceed 100',
            'must_pass_every_subject.boolean' => 'Must pass every subject must be true or false',
        ];
    }
}
