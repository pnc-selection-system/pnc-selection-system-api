<?php

namespace Services;

use App\Models\Candidate;
use App\Models\ExamOverallResult;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\ExamThreshold;
use App\Models\Province;
use App\Models\SelectCampaing;
use App\Services\ScoringEngine;

class ResultsService
{
    /**
     * Get all campaigns (rounds) that have exam results.
     *
     * @return array
     */
    public function getRounds(): array
    {
        $campaigns = SelectCampaing::whereHas('examResults')
            ->orWhereHas('examOverallResults')
            ->orderBy('year', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'year', 'status']);

        // Fallback: if no results exist yet, show active campaigns
        if ($campaigns->isEmpty()) {
            $campaigns = SelectCampaing::orderBy('year', 'desc')
                ->orderBy('name')
                ->get(['id', 'name', 'year', 'status']);
        }

        return $campaigns->map(fn ($c) => [
            'id'    => (string) $c->id,
            'label' => $c->name . ' (' . $c->year . ')',
        ])->values()->toArray();
    }

    /**
     * Get provinces that have candidates with results for a campaign.
     *
     * @param int $campaignId
     * @return array
     */
    public function getProvinces(int $campaignId): array
    {
        $provinces = Province::whereHas('candidates', function ($q) use ($campaignId) {
            $q->where('campaign_id', $campaignId)
              ->whereHas('examResults');
        })->get(['name']);

        $names = $provinces->pluck('name')->toArray();
        array_unshift($names, 'All provinces');

        return $names;
    }

    /**
     * Get summary statistics for a campaign.
     *
     * @param int $campaignId
     * @return array{satExam: int, passed: int, failed: int, passRate: int}
     */
    public function getSummary(int $campaignId): array
    {
        $overallResults = ExamOverallResult::where('campaign_id', $campaignId)->get();

        $satExam = $overallResults->count();
        $passed  = $overallResults->where('passed', true)->count();
        $failed  = $satExam - $passed;
        $passRate = $satExam > 0 ? round(($passed / $satExam) * 100) : 0;

        // Fallback: if no overall results, calculate from exam_results
        if ($satExam === 0) {
            $candidateIds = ExamResult::where('campaign_id', $campaignId)
                ->distinct('candidate_id')
                ->pluck('candidate_id');

            $satExam = $candidateIds->count();

            // Get overall threshold
            $overallThreshold = ExamThreshold::where('campaign_id', $campaignId)
                ->whereNull('subject_id')
                ->first();
            $overallPassScore = $overallThreshold ? (float) $overallThreshold->overall_pass_mark : null;

            $subjects = ExamSubject::where('campaign_id', $campaignId)->get();
            $totalWeight = $subjects->sum('weight');

            $passedCount = 0;
            foreach ($candidateIds as $cId) {
                $results = ExamResult::where('campaign_id', $campaignId)
                    ->where('candidate_id', $cId)
                    ->get()
                    ->keyBy('subject_id');

                // Calculate overall weighted percentage
                $totalWeighted = 0.0;
                foreach ($subjects as $subject) {
                    $result = $results->get($subject->id);
                    if ($result && $subject->max_score > 0) {
                        $pct = ($result->final_score / $subject->max_score) * 100;
                        $totalWeighted += ($pct * $subject->weight) / 100;
                    }
                }
                $overallPct = $totalWeight > 0 ? ($totalWeighted / $totalWeight) * 100 : 0;

                // Determine pass/fail using actual threshold
                $passedOverall = ScoringEngine::determinePassFail($overallPct, $overallPassScore);
                if ($passedOverall ?? false) {
                    $passedCount++;
                }
            }

            $passed = $passedCount;
            $failed = $satExam - $passed;
            $passRate = $satExam > 0 ? round(($passed / $satExam) * 100) : 0;
        }

        return [
            'satExam'  => $satExam,
            'passed'   => $passed,
            'failed'   => $failed,
            'passRate' => $passRate,
        ];
    }

    /**
     * Get score distribution for a campaign, optionally filtered by province.
     *
     * @param int    $campaignId
     * @param string $province
     * @return array{buckets: array, avg: float, median: float, passLine: float}
     */
    public function getScoreDistribution(int $campaignId, string $province = 'All provinces'): array
    {
        $overallResults = ExamOverallResult::where('campaign_id', $campaignId)
            ->when($province !== 'All provinces', function ($q) use ($province) {
                $q->whereHas('candidate', function ($cq) use ($province) {
                    $cq->whereHas('province', function ($pq) use ($province) {
                        $pq->where('name', $province);
                    });
                });
            })
            ->get();

        $percentages = $overallResults->pluck('overall_percentage')->filter()->values()->toArray();

        if (empty($percentages)) {
            // Fallback: calculate from exam_results
            $candidateIds = ExamResult::where('campaign_id', $campaignId)
                ->when($province !== 'All provinces', function ($q) use ($province) {
                    $q->whereHas('candidate', function ($cq) use ($province) {
                        $cq->whereHas('province', function ($pq) use ($province) {
                            $pq->where('name', $province);
                        });
                    });
                })
                ->distinct('candidate_id')
                ->pluck('candidate_id');

            $subjects = ExamSubject::where('campaign_id', $campaignId)->get();
            $totalWeight = $subjects->sum('weight');

            foreach ($candidateIds as $cId) {
                $results = ExamResult::where('campaign_id', $campaignId)
                    ->where('candidate_id', $cId)
                    ->get();

                $totalWeighted = 0;
                foreach ($results as $r) {
                    $subject = $subjects->firstWhere('id', $r->subject_id);
                    if ($subject && $subject->max_score > 0) {
                        $pct = ($r->final_score / $subject->max_score) * 100;
                        $totalWeighted += ($pct * $subject->weight) / 100;
                    }
                }
                $overallPct = $totalWeight > 0 ? round(($totalWeighted / $totalWeight) * 100, 2) : 0;
                $percentages[] = $overallPct;
            }
        }

        $avg = ! empty($percentages) ? round(array_sum($percentages) / count($percentages), 1) : 0;
        sort($percentages);
        $median = ! empty($percentages)
            ? round($percentages[(int) floor(count($percentages) / 2)], 1)
            : 0;

        // Get pass line from threshold
        $threshold = ExamThreshold::where('campaign_id', $campaignId)
            ->whereNull('subject_id')
            ->first();
        $passLine = $threshold ? (float) $threshold->overall_pass_mark : 0;

        // Build buckets
        $buckets = $this->buildBuckets($percentages, $passLine);

        return [
            'buckets'  => $buckets,
            'avg'      => $avg,
            'median'   => $median,
            'passLine' => $passLine,
        ];
    }

    /**
     * Get candidate result rows for the results table.
     *
     * @param int $campaignId
     * @return array
     */
    public function getResultsTable(int $campaignId): array
    {
        $subjects = ExamSubject::where('campaign_id', $campaignId)->get();
        $overallResults = ExamOverallResult::where('campaign_id', $campaignId)
            ->with('candidate')
            ->orderByDesc('overall_percentage')
            ->get();

        $rows = [];

        if ($overallResults->isNotEmpty()) {
            $rank = 1;
            foreach ($overallResults as $overall) {
                $candidate = $overall->candidate;
                if (! $candidate) {
                    continue;
                }

                $scores = [];
                $subjectResults = ExamResult::where('campaign_id', $campaignId)
                    ->where('candidate_id', $candidate->id)
                    ->get()
                    ->keyBy('subject_id');

                foreach ($subjects as $subject) {
                    $result = $subjectResults->get($subject->id);
                    $scores[$subject->name] = $result ? (float) $result->final_score : null;
                }

                $rows[] = [
                    'rank'      => $rank++,
                    'candidate' => $candidate->first_name . ' ' . $candidate->last_name,
                    'student_id'=> $candidate->student_id,
                    'scores'    => $scores,
                    'total'     => (float) $overall->overall_percentage,
                    'result'    => $overall->passed ? 'Pass' : 'Fail',
                ];
            }
        } else {
            // Fallback: calculate from exam_results directly
            $candidateIds = ExamResult::where('campaign_id', $campaignId)
                ->distinct('candidate_id')
                ->pluck('candidate_id');

            $candidates = Candidate::whereIn('id', $candidateIds)->get()->keyBy('id');
            $totalWeight = $subjects->sum('weight');

            $candidateScores = [];
            foreach ($candidateIds as $cId) {
                $results = ExamResult::where('campaign_id', $campaignId)
                    ->where('candidate_id', $cId)
                    ->get()
                    ->keyBy('subject_id');

                $scores = [];
                $totalWeighted = 0;

                foreach ($subjects as $subject) {
                    $result = $results->get($subject->id);
                    $score = $result ? (float) $result->final_score : 0;
                    $scores[$subject->name] = $score;

                    if ($subject->max_score > 0) {
                        $pct = ($score / $subject->max_score) * 100;
                        $totalWeighted += ($pct * $subject->weight) / 100;
                    }
                }

                $overallPct = $totalWeight > 0 ? round(($totalWeighted / $totalWeight) * 100, 2) : 0;
                $candidateScores[] = [
                    'candidate_id' => $cId,
                    'scores'       => $scores,
                    'total'        => $overallPct,
                ];
            }

            // Get overall threshold
            $overallThreshold = ExamThreshold::where('campaign_id', $campaignId)
                ->whereNull('subject_id')
                ->first();
            $overallPassScore = $overallThreshold ? (float) $overallThreshold->overall_pass_mark : null;

            // Sort by total descending and assign ranks
            usort($candidateScores, fn ($a, $b) => $b['total'] <=> $a['total']);

            $rank = 1;
            foreach ($candidateScores as $entry) {
                $candidate = $candidates->get($entry['candidate_id']);
                $passedOverall = ScoringEngine::determinePassFail($entry['total'], $overallPassScore);
                $rows[] = [
                    'rank'      => $rank++,
                    'candidate' => $candidate ? $candidate->first_name . ' ' . $candidate->last_name : 'Unknown',
                    'student_id'=> $candidate->student_id ?? '',
                    'scores'    => $entry['scores'],
                    'total'     => $entry['total'],
                    'result'    => ($passedOverall ?? false) ? 'Pass' : 'Fail',
                ];
            }
        }

        return $rows;
    }

    /**
     * Build score distribution buckets from percentages.
     */
    private function buildBuckets(array $percentages, float $passLine): array
    {
        $ranges = [
            ['min' => 0,  'max' => 20, 'label' => '0-20'],
            ['min' => 20, 'max' => 35, 'label' => '20-35'],
            ['min' => 35, 'max' => 50, 'label' => '35-50'],
            ['min' => 50, 'max' => 65, 'label' => '50-65'],
            ['min' => 65, 'max' => 75, 'label' => '65-75'],
            ['min' => 75, 'max' => 90, 'label' => '75-90'],
            ['min' => 90, 'max' => 100,'label' => '90-100'],
        ];

        $buckets = [];
        foreach ($ranges as $range) {
            $count = 0;
            foreach ($percentages as $pct) {
                if ($pct >= $range['min'] && $pct < $range['max']) {
                    $count++;
                }
            }
            // Also count exactly 100
            if ($range['max'] === 100) {
                foreach ($percentages as $pct) {
                    if ($pct == 100) {
                        $count++;
                    }
                }
            }

            $isModal = $passLine > 0 && $range['min'] <= $passLine && $passLine < $range['max'];

            $buckets[] = [
                'rangeLabel' => $range['label'],
                'count'      => $count,
                'isModal'    => $isModal,
            ];
        }

        return $buckets;
    }
}
