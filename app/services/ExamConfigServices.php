<?php

namespace Services;

use App\Models\ExamSubject;
use App\Models\ExamThreshold;
use App\Models\SelectCampaing;
use Illuminate\Database\Eloquent\Collection;
use Repositories\ExamThresholdRepository;

class ExamConfigServices
{
    public function __construct(protected ExamThresholdRepository $thresholdRepository) {}

    /**
     * Get full exam configuration for a campaign:
     * campaign details, subjects (with embedded thresholds), and overall threshold.
     */
    public function getConfig(int $campaignId): array
    {
        $campaign = SelectCampaing::findOrFail($campaignId);

        /** @var Collection|ExamSubject[] $subjects */
        $subjects = ExamSubject::where('campaign_id', $campaignId)->get();

        /** @var Collection|ExamThreshold[] $thresholds */
        $thresholds = $this->thresholdRepository->getByCampaign($campaignId);

        // Index thresholds by subject_id for fast lookup (null = overall)
        $thresholdIndex = [];
        foreach ($thresholds as $t) {
            $key = $t->subject_id ?? '__overall__';
            $thresholdIndex[$key] = [
                'id'         => $t->id,
                'pass_score' => (float) $t->pass_score,
            ];
        }

        // Attach per-subject threshold to each subject
        $subjectsData = $subjects->map(function (ExamSubject $s) use ($thresholdIndex) {
            $data = $s->toArray();
            $data['threshold'] = $thresholdIndex[$s->id] ?? null;
            // Cast numeric fields for clean JSON output
            $data['max_score'] = (float) $data['max_score'];
            $data['weight'] = (float) $data['weight'];

            return $data;
        });

        return [
            'campaign'    => $campaign,
            'subjects'    => $subjectsData,
            'thresholds'  => [
                'overall'      => $thresholdIndex['__overall__'] ?? null,
                'per_subject'  => $thresholds->filter(fn ($t) => $t->subject_id !== null)->values(),
            ],
        ];
    }

    /**
     * Upsert thresholds for a campaign and return the full updated config.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if campaign does not exist
     * @throws \Illuminate\Validation\ValidationException if subject_id does not belong to campaign
     */
    public function updateConfig(int $campaignId, array $data): array
    {
        // Validate campaign exists before any DB writes (avoids FK constraint QueryException)
        SelectCampaing::findOrFail($campaignId);

        // Validate that referenced subject_ids belong to this campaign
        if (isset($data['thresholds'])) {
            foreach ($data['thresholds'] as $t) {
                if (! empty($t['subject_id'])) {
                    $subject = ExamSubject::find($t['subject_id']);
                    if (! $subject || (int) $subject->campaign_id !== $campaignId) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "thresholds.{$t['subject_id']}.subject_id" => "Subject ID {$t['subject_id']} does not belong to campaign {$campaignId}.",
                        ]);
                    }
                }
            }
        }

        $this->thresholdRepository->bulkUpsert($campaignId, $data['thresholds']);

        return $this->getConfig($campaignId);
    }

    /**
     * Preview scoring impact for a set of sample scores.
     *
     * Given raw scores per subject, calculate:
     * - Per-subject weighted score and pass/fail status
     * - Overall weighted total and pass/fail status
     */
    public function previewScores(int $campaignId, array $data): array
    {
        $config = $this->getConfig($campaignId);
        $subjects = collect($config['subjects'])->keyBy('id');

        $results = [];
        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($data['scores'] as $entry) {
            $subjectId = (int) $entry['subject_id'];
            $rawScore = (float) $entry['raw_score'];

            $subject = $subjects->get($subjectId);
            if (! $subject) {
                continue;
            }

            $maxScore = (float) $subject['max_score'];
            $weight = (float) $subject['weight'];
            $deductionRules = $subject['deduction_rules'] ?? [];

            // Apply deduction rules to raw score
            $adjustedScore = $this->applyDeductions($rawScore, $maxScore, $deductionRules);
            $weightedScore = ($adjustedScore / max($maxScore, 1)) * $weight;

            $totalWeightedScore += $weightedScore;
            $totalWeight += $weight;

            // Per-subject threshold
            $subjectThreshold = $subject['threshold'];
            $passScore = $subjectThreshold['pass_score'] ?? null;
            $passed = $passScore !== null ? $adjustedScore >= $passScore : null;

            $results['subjects'][] = [
                'subject_id'     => $subjectId,
                'name'           => $subject['name'],
                'raw_score'      => $rawScore,
                'adjusted_score' => round($adjustedScore, 2),
                'max_score'      => $maxScore,
                'weight'         => $weight,
                'weighted_score' => round($weightedScore, 2),
                'pass_score'     => $passScore ? (float) $passScore : null,
                'passed'         => $passed,
            ];
        }

        // Normalise weighted total to percentage if weights are provided
        $overallPercentage = $totalWeight > 0
            ? ($totalWeightedScore / $totalWeight) * 100
            : 0;

        // Overall threshold
        $overallThreshold = $config['thresholds']['overall'];
        $overallPassScore = $overallThreshold['pass_score'] ?? null;
        $overallPassed = $overallPassScore !== null ? $overallPercentage >= $overallPassScore : null;

        $results['overall'] = [
            'total_weighted_score' => round($totalWeightedScore, 2),
            'total_weight'         => $totalWeight,
            'overall_percentage'   => round($overallPercentage, 2),
            'pass_score'           => $overallPassScore ? (float) $overallPassScore : null,
            'passed'               => $overallPassed,
        ];

        $results['thresholds_used'] = $config['thresholds'];

        return $results;
    }

    /**
     * Apply deduction rules to a raw score for preview purposes.
     *
     * The preview accepts already-computed raw scores (post-exam). Deduction rules
     * that affect per-question scoring (wrong_answer, partial_credit) require
     * question-level data not available in this preview context.
     *
     * Rules applied:
     * - negative_marking: if false, floor adjusted score at 0
     * - Score clamped to [0, maxScore]
     *
     * Rules reported but not simulated (require question-level input):
     * - wrong_answer: per-question penalty
     * - partial_credit: partial correctness multiplier
     * - unanswered: per-unanswered penalty
     */
    private function applyDeductions(float $rawScore, float $maxScore, array $rules): float
    {
        $adjusted = $rawScore;

        // If negative_marking is disabled, floor score at 0
        if (isset($rules['negative_marking'])) {
            // Explicitly check boolean false — if it's false, prevent negative scores
            if ($rules['negative_marking'] === false) {
                $adjusted = max(0, $adjusted);
            }
        }
        
        return max(0, min($adjusted, $maxScore));
    }
}
