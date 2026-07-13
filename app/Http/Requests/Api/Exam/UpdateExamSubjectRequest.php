<?php

namespace App\Http\Requests\Api\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamSubjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id'    => 'sometimes|required|integer|exists:selection_campaigns,id',
            'name'           => 'sometimes|required|string|max:100',
            'max_score'      => 'sometimes|required|numeric|min:0.01|max:9999.99',
            'weight'         => 'sometimes|required|numeric|min:0|max:100',
            'deduction_rules' => 'sometimes|array',
        ];
    }
}
