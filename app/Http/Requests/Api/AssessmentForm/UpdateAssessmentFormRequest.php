<?php

namespace App\Http\Requests\Api\AssessmentForm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id' => 'sometimes|required|integer|exists:selection_campaigns,id',
            'name'        => 'sometimes|required|string|max:100',
            'schema'      => 'sometimes|required|array',
        ];
    }
}
