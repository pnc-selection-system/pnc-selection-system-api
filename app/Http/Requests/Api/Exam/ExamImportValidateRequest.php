<?php

namespace App\Http\Requests\Api\Exam;

use Illuminate\Foundation\Http\FormRequest;

class ExamImportValidateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'column_mapping'                       => 'required|array',
            'column_mapping.student_name'          => 'required|integer|min:0',
            'column_mapping.student_id'            => 'nullable|integer|min:0',
            'column_mapping.subject_name'          => 'required|integer|min:0',
            'column_mapping.score'                 => 'required|integer|min:0',
            'column_mapping.total_questions'       => 'nullable|integer|min:0',
            'column_mapping.correct_count'         => 'nullable|integer|min:0',
            'column_mapping.wrong_count'           => 'nullable|integer|min:0',
            'column_mapping.unanswered_count'      => 'nullable|integer|min:0',
            'column_mapping.percentage'            => 'nullable|integer|min:0',
            'column_mapping.grade'                 => 'nullable|integer|min:0',
            'column_mapping.exam_date'             => 'nullable|integer|min:0',
        ];
    }
}
