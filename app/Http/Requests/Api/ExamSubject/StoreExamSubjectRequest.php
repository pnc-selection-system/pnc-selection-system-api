<?php

namespace App\Http\Requests\Api\ExamSubject;

use App\Models\ExamSubject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreExamSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id'    => ['required', 'integer', 'exists:selection_campaigns,id'],
            'name'           => ['required', 'string', 'max:100'],
            'max_score'      => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'weight'         => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'rules'          => ['nullable', 'array'],
            'rules.*.name'   => ['required', 'string', 'max:100'],
            'rules.*.desc'   => ['nullable', 'string'],
            'rules.*.sign'   => ['required', 'in:+,-,*,%'],
            'rules.*.value'  => ['required', 'numeric', 'min:0', 'max:999.99'],
            'rules.*.status' => ['nullable', 'in:active,inactive'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $campaignId = (int) $this->input('campaign_id');
                $newWeight = (float) ($this->input('weight') ?? 0);

                $currentTotalWeight = ExamSubject::where('campaign_id', $campaignId)
                    ->where('is_delete', false)
                    ->sum('weight');

                $newTotal = $currentTotalWeight + $newWeight;

                if ($newTotal > 100) {
                    $validator->errors()->add(
                        'weight',
                        "Total weight would exceed 100%. Current total: {$currentTotalWeight}% + new: {$newWeight}% = {$newTotal}%"
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'campaign_id.required' => 'The campaign is required.',
            'campaign_id.exists'   => 'The selected campaign does not exist.',
            'name.required'        => 'The subject name is required.',
            'name.max'             => 'The subject name must not exceed 100 characters.',
            'max_score.required'   => 'The maximum score is required.',
            'max_score.numeric'    => 'The maximum score must be a number.',
            'max_score.min'        => 'The maximum score must be at least 0.01.',
        ];
    }
}
