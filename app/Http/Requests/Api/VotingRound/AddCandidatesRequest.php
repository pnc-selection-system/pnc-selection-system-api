<?php

namespace App\Http\Requests\Api\VotingRound;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class AddCandidatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_ids' => 'required|array|min:1',
            'candidate_ids.*' => 'required|integer|exists:candidates,id',
        ];
    }

    /**
     * Validate that all candidates have passed interest assessment AND home investigation.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $candidateIds = $this->input('candidate_ids', []);

            foreach ($candidateIds as $id) {
                $passedAssessment = DB::table('assessment_responses')
                    ->where('candidate_id', $id)
                    ->where('passed', true)
                    ->exists();

                $passedInvestigation = DB::table('home_investigations')
                    ->where('candidate_id', $id)
                    ->where('recommendation', 'Recommend')
                    ->exists();

                if (! $passedAssessment) {
                    $validator->errors()->add(
                        "candidate_ids.{$id}",
                        "Candidate #{$id} has not passed the interest assessment."
                    );
                }

                if (! $passedInvestigation) {
                    $validator->errors()->add(
                        "candidate_ids.{$id}",
                        "Candidate #{$id} has not been recommended by home investigation."
                    );
                }
            }
        });
    }
}
