<?php

namespace App\Http\Requests\Api\AssessmentResponse;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => 'required|integer|exists:assessment_forms,id',
            'candidate_id' => 'required|integer|exists:candidates,id',
            'answers' => 'required|array',
        ];
    }
}
