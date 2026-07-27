<?php

namespace App\Http\Requests\Api\VotingRound;

use Illuminate\Foundation\Http\FormRequest;

class StoreVotingRoundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => 'required|integer|exists:selection_campaigns,id',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'name' => 'required|string|max:100',
            'voting_method' => 'required|string|in:Majority,Weighted',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'quorum' => 'nullable|integer|min:0',
            'pass_threshold' => 'nullable|integer|min:0|max:100',
            'waitlist_cap' => 'nullable|integer|min:0',
            'total_members' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'province_id.exists' => 'Selected province does not exist.',
            'district_id.exists' => 'Selected district does not exist.',
        ];
    }
}
