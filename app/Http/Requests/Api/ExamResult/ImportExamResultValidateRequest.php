<?php

namespace App\Http\Requests\Api\ExamResult;

use Illuminate\Foundation\Http\FormRequest;

class ImportExamResultValidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'import_file_id' => ['required', 'integer', 'exists:import_files,id'],
            'column_mapping' => ['required', 'array'],
            'column_mapping.*' => ['required', 'string'],
            'subject_id' => ['required', 'integer', 'exists:exam_subjects,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'import_file_id.required' => 'Import file ID is required',
            'import_file_id.integer'  => 'Import file ID must be an integer',
            'import_file_id.exists'   => 'Import file not found',
            'column_mapping.required' => 'Column mapping is required',
            'column_mapping.array'    => 'Column mapping must be an array',
            'subject_id.required'     => 'Subject ID is required',
            'subject_id.integer'      => 'Subject ID must be an integer',
            'subject_id.exists'       => 'Subject not found',
        ];
    }
}
