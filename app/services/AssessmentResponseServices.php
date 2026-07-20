<?php

namespace Services;

use App\Models\AssessmentQuestion;
use App\Models\AssessmentRespone;
use Illuminate\Support\Collection;
use Repositories\AssessmentResponseRepository;

class AssessmentResponseServices
{
    public function __construct(protected AssessmentResponseRepository $assessmentResponseRepository) {}

    public function list(array $filters = []): Collection
    {
        return $this->assessmentResponseRepository->list($filters);
    }

    /**
     * Submit answers for a candidate.
     * $answers = [ ['question_id' => 1, 'answer' => 'value'], ... ]
     */
    public function submit(int $candidateId, array $answers): array
    {
        foreach ($answers as $item) {
            AssessmentRespone::updateOrCreate(
                ['candidate_id' => $candidateId, 'question_id' => $item['question_id']],
                ['answer'       => $item['answer']]
            );
        }

        return $this->findByCandidate($candidateId);
    }

    /**
     * Get all responses for a candidate, including unanswered questions.
     */
    public function findByCandidate(int $candidateId): array
    {
        $rows = AssessmentRespone::with(['candidate.province', 'question.form'])
            ->where('candidate_id', $candidateId)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        return $this->assessmentResponseRepository->groupRows($rows);
    }

    public function deleteByCandidate(int $candidateId): void
    {
        AssessmentRespone::where('candidate_id', $candidateId)->delete();
    }
}
