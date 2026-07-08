<?php

namespace App\Http\Requests\Api\AssessmentForm;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id' => 'required|integer|exists:selection_campaigns,id',
            'name'        => 'required|string|max:100',
            'schema'      => 'required|array',
        ];
    }
}
