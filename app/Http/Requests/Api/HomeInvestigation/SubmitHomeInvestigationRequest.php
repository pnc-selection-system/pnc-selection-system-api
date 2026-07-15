<?php

namespace App\Http\Requests\Api\HomeInvestigation;

use Illuminate\Foundation\Http\FormRequest;

class SubmitHomeInvestigationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'people_met' => ['required', 'string'],
            'observations' => ['required', 'string'],
            'findings' => ['required', 'string'],
            'recommendation' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'people_met.required' => 'The people met field is required.',
            'observations.required' => 'The observations field is required.',
            'findings.required' => 'The findings field is required.',
            'recommendation.required' => 'The recommendation field is required.',
        ];
    }
}