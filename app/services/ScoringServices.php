<?php

namespace Services;

use App\Models\Cadidate;
use App\Models\ExamOverallResult;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\ExamThreshold;
use App\Services\ScoringEngine;

class ScoringServices
{
    /**
     * Calculate results for a single candidate across all campaign subjects.
     *
     * This is idempotent — existing results are overwritten on each call,
     * making it safe for audited recalculation.
     *
     * @param int $campaignId
     * @param int $candidateId
     * @param array $scores Array of subject scores:
     *                      [{ subject_id, raw_score, correct_count, wrong_count, unanswered_count }]
     *
     * @return array{ subject_results: array, overall_result: array }
     */
    public function calculateAndStore(int $campaignId, int $candidateId, array $scores): array
    {
        // Load subjects and thresholds for this campaign
        $subjects = ExamSubject::where('campaign_id', $campaignId)->get()->keyBy('id');
        $thresholds = ExamThreshold::where('campaign_id', $campaignId)->get()->keyBy('subject_id');

        $overallThreshold = ExamThreshold::where('campaign_id', $campaignId)
            ->whereNull('subject_id')
            ->first();

        $subjectResults = [];
        $overallInput = [];

        foreach ($scores as $entry) {
            $subjectId = (int) ($entry['subject_id'] ?? 0);
            $subject = $subjects->get($subjectId);

            if (! $subject) {
                continue;
            }

            // Use the pure scoring engine
            $calculated = ScoringEngine::calculateSubjectScore(
                rawScore:        (float) ($entry['raw_score'] ?? 0),
                correctCount:    (int) ($entry['correct_count'] ?? 0),
                wrongCount:      (int) ($entry['wrong_count'] ?? 0),
                unansweredCount: (int) ($entry['unanswered_count'] ?? 0),
                maxScore:        (float) $subject->max_score,
                deductionRules:  []
            );

            $subjectThreshold = $thresholds->get($subjectId);
            $passScore = $subjectThreshold ? (float) $subjectThreshold->per_subject_min : null;
            $passed = ScoringEngine::determinePassFail(
                $calculated['final_score'],
                $passScore
            );

            // Upsert subject result
            $examResult = ExamResult::updateOrCreate(
                [
                    'candidate_id' => $candidateId,
                    'subject_id'   => $subjectId,
                ],
                [
                    'campaign_id'  => $campaignId,
                    'raw_correct'  => (int) ($entry['correct_count'] ?? 0),
                    'raw_wrong'    => (int) ($entry['wrong_count'] ?? 0),
                    'raw_score'    => $calculated['raw_score'],
                    'deduction'    => $calculated['deduction'],
                    'final_score'  => $calculated['final_score'],
                    'passed'       => $passed ?? false,
                ]
            );

            $subjectResults[] = [
                'subject_id'   => $subjectId,
                'subject_name' => $subject->name,
                'max_score'    => (float) $subject->max_score,
                'weight'       => (float) $subject->weight,
                'raw_score'    => $calculated['raw_score'],
                'deduction'    => $calculated['deduction'],
                'final_score'  => $calculated['final_score'],
                'passed'       => $passed,
            ];

            $overallInput[] = [
                'final_score' => $calculated['final_score'],
                'max_score'   => (float) $subject->max_score,
                'weight'      => (float) $subject->weight,
            ];
        }

        // Calculate overall weighted score
        $overall = ScoringEngine::calculateOverallScore($overallInput);
        $overallPassed = ScoringEngine::determinePassFail(
            $overall['overall_percentage'],
            $overallThreshold ? (float) $overallThreshold->overall_pass_mark : null
        );

        // Store overall result
        $overallResult = ExamOverallResult::updateOrCreate(
            [
                'candidate_id' => $candidateId,
                'campaign_id'  => $campaignId,
            ],
            [
                'total_weighted_score' => $overall['total_weighted_score'],
                'overall_percentage'   => $overall['overall_percentage'],
                'passed'               => $overallPassed ?? false,
            ]
        );

        return [
            'subject_results' => $subjectResults,
            'overall_result'  => [
                'total_weighted_score' => $overall['total_weighted_score'],
                'total_weight'         => $overall['total_weight'],
                'overall_percentage'   => $overall['overall_percentage'],
                'passed'               => $overallPassed,
            ],
        ];
    }

    /**
     * Recalculate all candidates for a given campaign.
     * Useful for batch recalculation after rule/weight changes.
     */
    public function recalculateCampaign(int $campaignId): array
    {
        $candidates = Cadidate::where('campaign_id', $campaignId)->get();
        $results = [];

        foreach ($candidates as $candidate) {
            $existingScores = ExamResult::where('campaign_id', $campaignId)
                ->where('candidate_id', $candidate->id)
                ->get();

            if ($existingScores->isEmpty()) {
                continue;
            }

            $scores = $existingScores->map(fn ($r) => [
                'subject_id'       => $r->subject_id,
                'raw_score'        => (float) $r->raw_score,
                'correct_count'    => (int) $r->raw_correct,
                'wrong_count'      => (int) $r->raw_wrong,
                'unanswered_count' => 0, // Not stored per-row; default to 0
            ])->toArray();

            $result = $this->calculateAndStore($campaignId, $candidate->id, $scores);
            $results[$candidate->id] = $result['overall_result'];
        }

        return $results;
    }
}
