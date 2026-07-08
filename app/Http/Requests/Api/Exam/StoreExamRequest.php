<?php

namespace App\Http\Requests\Api\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id'    => 'required|integer|exists:selection_campaigns,id',
            'exam_date'      => 'required|date',
            'publish_status' => 'sometimes|boolean',
        ];
    }
}
