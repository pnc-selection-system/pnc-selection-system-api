<?php

namespace App\Http\Requests\Api\VotingRound;

use Illuminate\Foundation\Http\FormRequest;

class CastVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => 'required|string|in:Approve,Reject,Abstain',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
