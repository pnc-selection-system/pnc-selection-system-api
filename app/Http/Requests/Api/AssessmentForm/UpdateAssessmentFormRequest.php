<?php

namespace App\Http\Requests\Api\AssessmentForm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'campaign_id' => 'sometimes|required|integer|exists:selection_campaigns,id',
            'name' => 'sometimes|required|string|max:100',
            'pass_threshold' => 'sometimes|numeric|min:0|max:100',
            'schema' => 'sometimes|required|array',
            'schema.fields' => 'sometimes|required|array|min:1',
            'schema.fields.*.key' => 'required|string|alpha_dash|distinct',
            'schema.fields.*.label' => 'required|string|max:255',
            'schema.fields.*.type' => 'required|in:text,textarea,number,rating,select,radio,checkbox,date,short_text,scale_1_5,single_choice,multi_choice',
            'schema.fields.*.options' => 'sometimes|required_if:schema.fields.*.type,select,radio,checkbox,single_choice,multi_choice|array',
            'schema.fields.*.options.*' => 'required|string',
            'schema.fields.*.rules' => 'sometimes|array',
            'schema.fields.*.rules.required' => 'sometimes|boolean',
            'schema.fields.*.rules.min' => 'sometimes|numeric',
            'schema.fields.*.rules.max' => 'sometimes|numeric',
            'schema.fields.*.rules.regex' => 'sometimes|string',
            'schema.fields.*.rules.in' => 'sometimes|array',
            'schema.fields.*.rules.in.*' => 'sometimes|string',
            'schema.fields.*.weight' => 'required|numeric|min:0|max:100',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->has('schema.fields')) {
                return;
            }

            $fields = $this->input('schema.fields', []);
            $total = array_sum(array_column($fields, 'weight'));

            if ($total > 100) {
                $validator->errors()->add('schema.fields', "Total scoring weight must not exceed 100 (got {$total}).");
            }
        });
    }
}
