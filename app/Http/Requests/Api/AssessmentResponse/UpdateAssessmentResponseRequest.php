<?php

namespace App\Http\Requests\Api\AssessmentResponse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => 'sometimes|required|array',
            'total_score' => 'sometimes|numeric|min:0|max:100',
        ];
    }
}
