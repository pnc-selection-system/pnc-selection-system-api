<?php

namespace App\Http\Requests\Api\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamSubjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id'    => 'required|integer|exists:selection_campaigns,id',
            'name'           => 'required|string|max:100',
            'max_score'      => 'required|numeric|min:0.01|max:9999.99',
            'weight'         => 'required|numeric|min:0|max:100',
            'deduction_rules' => 'sometimes|array',
        ];
    }
}
