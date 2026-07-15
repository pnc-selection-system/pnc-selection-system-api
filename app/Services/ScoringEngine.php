<?php

namespace App\Services;

/**
 * Pure, stateless scoring engine for exam score calculations.
 *
 * All methods are static and free of side effects — perfect for
 * audited recalculation, unit testing, and reuse across services.
 */
class ScoringEngine
{
    /**
     * Calculate a subject's final score after applying deduction rules.
     *
     * Deduction rules supported (from ExamSubject.deduction_rules JSONB):
     * - wrong_answer: per-wrong-answer penalty (e.g. -0.25)
     * - unanswered: per-unanswered penalty (e.g. 0 or -0.5)
     * - negative_marking: bool — if false, final score floors at 0
     * - partial_credit: multiplier for partial correctness (0-1)
     *
     * @param float $rawScore        The student's raw score (points earned)
     * @param int   $correctCount    Number of correctly answered questions
     * @param int   $wrongCount      Number of incorrectly answered questions
     * @param int   $unansweredCount Number of unanswered questions
     * @param float $maxScore        Maximum possible score for the subject
     * @param array $deductionRules  Deduction rules from ExamSubject
     *
     * @return array{
     *     raw_score: float,
     *     deduction: float,
     *     final_score: float,
     *     negative_marking_applied: bool,
     * }
     */
    public static function calculateSubjectScore(
        float $rawScore,
        int $correctCount,
        int $wrongCount,
        int $unansweredCount,
        float $maxScore,
        array $deductionRules = []
    ): array {
        $ruleWrongAnswer = (float) ($deductionRules['wrong_answer'] ?? 0);
        $ruleUnanswered  = (float) ($deductionRules['unanswered'] ?? 0);
        $negativeMarking = (bool) ($deductionRules['negative_marking'] ?? true);
        $partialCredit   = (float) ($deductionRules['partial_credit'] ?? 1.0);

        // Step 1: Calculate deduction from wrong answers and unanswered
        $deduction = ($wrongCount * $ruleWrongAnswer) + ($unansweredCount * $ruleUnanswered);

        // Step 2: Apply partial credit multiplier to the raw score
        // (partial_credit of 1.0 = full credit for correct answers)
        $adjustedRaw = $rawScore * $partialCredit;

        // Step 3: Calculate final score = adjusted raw + deduction (which is <= 0)
        $finalScore = $adjustedRaw + $deduction;

        // Step 4: Apply negative_marking guard
        $negativeApplied = false;
        if (! $negativeMarking && $finalScore < 0) {
            $finalScore = 0;
            $negativeApplied = true;
        }

        // Step 5: Clamp to [0, maxScore]
        $finalScore = max(0, min($finalScore, $maxScore));

        return [
            'raw_score'                 => round($rawScore, 2),
            'deduction'                 => round($deduction, 2),
            'final_score'               => round($finalScore, 2),
            'negative_marking_applied'  => $negativeApplied,
        ];
    }

    /**
     * Calculate the weighted contribution of a subject's final score
     * toward the overall campaign score.
     *
     * @param float $finalScore Score after deductions
     * @param float $maxScore   Maximum possible score for the subject
     * @param float $weight     Subject weight (e.g. 50.0 = 50%)
     *
     * @return float Weighted score contribution
     */
    public static function calculateWeightedScore(
        float $finalScore,
        float $maxScore,
        float $weight
    ): float {
        if ($maxScore <= 0 || $weight <= 0) {
            return 0.0;
        }

        $percentage = ($finalScore / $maxScore) * 100;

        return round(($percentage * $weight) / 100, 2);
    }

    /**
     * Calculate overall weighted score across multiple subjects.
     *
     * @param array $subjects Array of arrays, each containing:
     *                        'final_score', 'max_score', 'weight'
     *
     * @return array{
     *     total_weighted_score: float,
     *     total_weight: float,
     *     overall_percentage: float,
     * }
     */
    public static function calculateOverallScore(array $subjects): array
    {
        $totalWeighted = 0.0;
        $totalWeight   = 0.0;

        foreach ($subjects as $subject) {
            $weighted = self::calculateWeightedScore(
                (float) ($subject['final_score'] ?? 0),
                (float) ($subject['max_score'] ?? 1),
                (float) ($subject['weight'] ?? 0)
            );

            $totalWeighted += $weighted;
            $totalWeight   += (float) ($subject['weight'] ?? 0);
        }

        $overallPct = $totalWeight > 0
            ? round(($totalWeighted / $totalWeight) * 100, 2)
            : 0.0;

        return [
            'total_weighted_score' => round($totalWeighted, 2),
            'total_weight'         => round($totalWeight, 2),
            'overall_percentage'   => $overallPct,
        ];
    }

    /**
     * Determine pass/fail for a score against a threshold.
     *
     * @param float      $score     The score to check (final or weighted)
     * @param float|null $passScore The passing threshold (null = no threshold set)
     *
     * @return bool|null True if passed, false if failed, null if no threshold
     */
    public static function determinePassFail(float $score, ?float $passScore): ?bool
    {
        if ($passScore === null) {
            return null;
        }

        return $score >= $passScore;
    }
}
