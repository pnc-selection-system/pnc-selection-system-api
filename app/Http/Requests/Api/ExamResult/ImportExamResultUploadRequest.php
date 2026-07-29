<?php

namespace App\Http\Requests\Api\ExamResult;

use Illuminate\Foundation\Http\FormRequest;

class ImportExamResultUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:10240'],
            'campaign_id' => ['required', 'integer', 'exists:selection_campaigns,id'],
            'subject_id' => ['required', 'integer', 'exists:exam_subjects,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'A file is required',
            'file.file' => 'The uploaded file must be a valid file',
            'file.mimes' => 'The file must be a CSV, XLSX, or XLS file',
            'file.max' => 'The file may not be larger than 10MB',
            'campaign_id.required' => 'Campaign ID is required',
            'campaign_id.integer' => 'Campaign ID must be an integer',
            'campaign_id.exists' => 'Campaign not found',
            'subject_id.required' => 'Subject ID is required',
            'subject_id.integer' => 'Subject ID must be an integer',
            'subject_id.exists' => 'Subject not found',
        ];
    }
}
