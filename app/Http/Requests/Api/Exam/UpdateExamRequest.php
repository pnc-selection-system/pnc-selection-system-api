<?php

namespace App\Http\Requests\Api\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id' => 'sometimes|required|integer|exists:selection_campaigns,id',
            'exam_date' => 'sometimes|required|date',
            'publish_status' => 'sometimes|boolean',
        ];
    }
}
