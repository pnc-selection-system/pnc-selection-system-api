<?php

namespace App\Http\Requests\Api\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'thresholds'              => 'required|array|min:1',
            'thresholds.*.subject_id' => 'nullable|integer|exists:exam_subjects,id',
            'thresholds.*.pass_score' => 'required|numeric|min:0|max:9999.99',
        ];
    }
}
