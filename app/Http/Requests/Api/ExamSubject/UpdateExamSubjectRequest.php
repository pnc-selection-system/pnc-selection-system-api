<?php

namespace App\Http\Requests\Api\ExamSubject;

use App\Models\ExamSubject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateExamSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id'    => ['nullable', 'integer', 'exists:selection_campaigns,id'],
            'name'           => ['nullable', 'string', 'max:100'],
            'max_score'      => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'weight'         => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'rules'          => ['nullable', 'array'],
            'rules.*.id'     => ['nullable', 'integer'],
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
                /** @var ExamSubject $examSubject */
                $examSubject = $this->route('exam_subject');

                if (! $examSubject) {
                    return;
                }

                $newWeight = $this->has('weight')
                    ? (float) $this->input('weight')
                    : (float) $examSubject->weight;

                $currentTotalWeight = ExamSubject::where('campaign_id', $examSubject->campaign_id)
                    ->where('is_delete', false)
                    ->where('id', '!=', $examSubject->id)
                    ->sum('weight');

                $newTotal = $currentTotalWeight + $newWeight;

                if ($newTotal > 100) {
                    $validator->errors()->add(
                        'weight',
                        "Total weight would exceed 100%. Current total (excluding this subject): {$currentTotalWeight}% + new: {$newWeight}% = {$newTotal}%"
                    );
                }
            },
        ];
    }
}
