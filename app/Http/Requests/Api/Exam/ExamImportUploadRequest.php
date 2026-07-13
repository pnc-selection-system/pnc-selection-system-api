<?php

namespace App\Http\Requests\Api\Exam;

use Illuminate\Foundation\Http\FormRequest;

class ExamImportUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file'       => 'required|file|mimes:csv,txt,xlsx|max:10240', // 10MB max
            'campaign_id' => 'required|integer|exists:selection_campaigns,id',
        ];
    }
}
