<?php

namespace App\Http\Requests\Api\ExamSubject;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id'    => ['nullable', 'integer', 'exists:selection_campaigns,id'],
            'name'           => ['nullable', 'string', 'max:100'],
            'max_score'      => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'weight'         => ['nullable', 'numeric', 'min:0', 'max:99.99'],
        ];
    }
}
