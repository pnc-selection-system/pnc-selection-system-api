<?php

namespace App\Http\Requests\Api\Exam;

use Illuminate\Foundation\Http\FormRequest;

class PreviewExamConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'scores'              => 'required|array|min:1',
            'scores.*.subject_id' => 'required|integer|exists:exam_subjects,id',
            'scores.*.raw_score'  => 'required|numeric|min:0',
        ];
    }
}
