<?php

namespace Services;

use App\Enums\CandidateStatus;
use App\Enums\ReviewStatus;
use App\Models\AuditLoge;
use App\Models\Cadidate;
use App\Models\ExamOverallResult;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\ExamThreshold;
use App\Models\ResultSnapshot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamResultServices
{
    /**
     * Get the overall ranking and per-subject ranking for a campaign.
     *
     * Rankings are computed dynamically using SQL window functions
     * (or in-memory sorting for simplicity).
     *
     * @return array{
     *     campaign_id: int,
     *     overall: array,     // sorted by rank asc
     *     subjects: array,    // keyed by subject_id
     * }
     */
    public function getRanking(int $campaignId): array
    {
        // --- Overall ranking from exam_overall_results ---
        $overallResults = ExamOverallResult::where('campaign_id', $campaignId)
            ->with('candidate:id,first_name,last_name')
            ->orderByDesc('overall_percentage')
            ->get();

        $overallRanking = [];
        $rank = 1;
        foreach ($overallResults as $result) {
            $candidate = $result->candidate;
            $overallRanking[] = [
                'rank'                => $rank++,
                'candidate_id'        => $candidate->id,
                'candidate_name'      => $candidate->first_name . ' ' . $candidate->last_name,
                'total_weighted_score'=> (float) $result->total_weighted_score,
                'overall_percentage'  => (float) $result->overall_percentage,
                'passed'              => $result->passed,
            ];
        }

        // --- Per-subject ranking from exam_results ---
        $subjects = ExamSubject::where('campaign_id', $campaignId)
            ->select('id', 'name', 'max_score', 'weight')
            ->get()
            ->keyBy('id');

        $subjectResults = ExamResult::where('exam_results.campaign_id', $campaignId)
            ->join('candidates', 'exam_results.candidate_id', '=', 'candidates.id')
            ->select(
                'exam_results.subject_id',
                'exam_results.candidate_id',
                'exam_results.final_score',
                'exam_results.raw_score',
                'exam_results.deduction',
                'exam_results.passed',
                DB::raw("CONCAT(candidates.first_name, ' ', candidates.last_name) as candidate_name")
            )
            ->orderBy('exam_results.subject_id')
            ->orderByDesc('exam_results.final_score')
            ->get()
            ->groupBy('subject_id');

        $subjectsRanking = [];
        foreach ($subjects as $subjectId => $subject) {
            $rows = $subjectResults->get($subjectId, collect());
            $entries = [];
            $rank = 1;
            foreach ($rows as $row) {
                $entries[] = [
                    'rank'           => $rank++,
                    'candidate_id'   => (int) $row->candidate_id,
                    'candidate_name' => $row->candidate_name,
                    'final_score'    => (float) $row->final_score,
                    'raw_score'      => (float) $row->raw_score,
                    'deduction'      => (float) $row->deduction,
                    'passed'         => (bool) $row->passed,
                ];
            }

            $subjectsRanking[] = [
                'subject_id'   => $subjectId,
                'subject_name' => $subject->name,
                'max_score'    => (float) $subject->max_score,
                'weight'       => (float) $subject->weight,
                'rankings'     => $entries,
            ];
        }

        return [
            'campaign_id' => $campaignId,
            'overall'     => $overallRanking,
            'subjects'    => $subjectsRanking,
        ];
    }

    /**
     * Apply thresholds to automatically set candidate EXAM_PASSED / EXAM_FAILED status.
     *
     * Logic:
     * 1. Load all candidates with exam overall results for the campaign.
     * 2. Load per-subject thresholds and overall threshold.
     * 3. For each candidate:
     *    - If overall threshold exists and overall result is FAILED → EXAM_FAILED
     *    - If per-subject thresholds exist and ANY subject is FAILED → EXAM_FAILED
     *    - Otherwise → EXAM_PASSED
     * 4. Update Cadidate.status accordingly.
     *
     * @return array{ updated: int, passed: int, failed: int }
     */
    public function applyThresholds(int $campaignId): array
    {
        // Load thresholds
        $thresholds = ExamThreshold::where('campaign_id', $campaignId)->get();
        $overallThreshold = $thresholds->firstWhere('subject_id', null);
        $subjectThresholds = $thresholds->filter(fn ($t) => $t->subject_id !== null)->keyBy('subject_id');

        // No thresholds configured at all — nothing to do
        if ($thresholds->isEmpty()) {
            return ['updated' => 0, 'passed' => 0, 'failed' => 0];
        }

        // Load overall results
        $overallResults = ExamOverallResult::where('campaign_id', $campaignId)
            ->get()
            ->keyBy('candidate_id');

        // Load per-subject results
        $subjectResults = ExamResult::where('campaign_id', $campaignId)
            ->select('candidate_id', 'subject_id', 'final_score', 'passed')
            ->get()
            ->groupBy('candidate_id');

        $passed = 0;
        $failed = 0;

        // Update each candidate's status using a chunked update approach
        $candidates = Cadidate::where('campaign_id', $campaignId)->get();

        foreach ($candidates as $candidate) {
            $overallResult = $overallResults->get($candidate->id);

            // Skip candidates without any exam results
            if (! $overallResult) {
                continue;
            }

            $overallPassed = true;
            if ($overallThreshold) {
                $overallPassed = $overallResult->passed;
            }

            // Check per-subject thresholds
            $subjectsPassed = true;
            if ($subjectThresholds->isNotEmpty()) {
                $candidateSubjectResults = $subjectResults->get($candidate->id, collect());
                foreach ($subjectThresholds as $subjectId => $threshold) {
                    $subjectResult = $candidateSubjectResults->firstWhere('subject_id', $subjectId);
                    if (! $subjectResult || ! $subjectResult->passed) {
                        $subjectsPassed = false;
                        break;
                    }
                }
            }

            $isPassed = $overallPassed && $subjectsPassed;

            $candidate->status = $isPassed
                ? CandidateStatus::ExamPassed->value
                : CandidateStatus::ExamFailed->value;
            $candidate->save();

            if ($isPassed) {
                $passed++;
            } else {
                $failed++;
            }
        }

        return [
            'updated' => $passed + $failed,
            'passed'  => $passed,
            'failed'  => $failed,
        ];
    }

    /**
     * Get campaign-level exam statistics: pass rate, average, and by-province breakdown.
     *
     * @return array{
     *     campaign_id: int,
     *     total_candidates: int,
     *     passed_count: int,
     *     failed_count: int,
     *     pass_rate: float|null,
     *     average_overall_percentage: float|null,
     *     min_overall_percentage: float|null,
     *     max_overall_percentage: float|null,
     *     by_province: array,
     * }
     */
    public function getStats(int $campaignId): array
    {
        $overallResults = ExamOverallResult::where('campaign_id', $campaignId)
            ->with('candidate:id,province_id')
            ->get();

        $totalCandidates = $overallResults->count();
        $passedCount = $overallResults->where('passed', true)->count();
        $failedCount = $overallResults->where('passed', false)->count();
        $passRate = $totalCandidates > 0
            ? round(($passedCount / $totalCandidates) * 100, 2)
            : null;

        $percentages = $overallResults->pluck('overall_percentage')->filter();
        $avgPercentage = $percentages->isNotEmpty()
            ? round($percentages->avg(), 2)
            : null;
        $minPercentage = $percentages->isNotEmpty()
            ? round($percentages->min(), 2)
            : null;
        $maxPercentage = $percentages->isNotEmpty()
            ? round($percentages->max(), 2)
            : null;

        // --- By-province breakdown ---
        $provinceStats = $overallResults->groupBy(function ($result) {
            return $result->candidate->province_id ?? '__unknown__';
        })->map(function (Collection $results, $provinceId) {
            $total = $results->count();
            $passed = $results->where('passed', true)->count();
            $failed = $total - $passed;
            $percentages = $results->pluck('overall_percentage')->filter();

            return [
                'province_id'     => $provinceId === '__unknown__' ? null : (int) $provinceId,
                'total'           => $total,
                'passed'          => $passed,
                'failed'          => $failed,
                'pass_rate'       => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
                'avg_percentage'  => $percentages->isNotEmpty() ? round($percentages->avg(), 2) : null,
            ];
        })->values();

        // Enrich province names
        $provinceIds = $provinceStats->pluck('province_id')->filter()->toArray();
        $provinces = \App\Models\Province::whereIn('id', $provinceIds)
            ->select('id', 'name')
            ->get()
            ->keyBy('id');

        $byProvince = $provinceStats->map(function ($stat) use ($provinces) {
            $province = $stat['province_id'] ? $provinces->get($stat['province_id']) : null;
            return array_merge($stat, [
                'province_name' => $province ? $province->name : 'Unknown',
            ]);
        });

        return [
            'campaign_id'                => $campaignId,
            'total_candidates'           => $totalCandidates,
            'passed_count'               => $passedCount,
            'failed_count'               => $failedCount,
            'pass_rate'                  => $passRate,
            'average_overall_percentage' => $avgPercentage,
            'min_overall_percentage'     => $minPercentage,
            'max_overall_percentage'     => $maxPercentage,
            'by_province'                => $byProvince,
        ];
    }

    // -----------------------------------------------------------------------
    //  Publish / Lock / Recalculate
    // -----------------------------------------------------------------------

    /**
     * Publish (lock) all exam results for a campaign.
     *
     * Transitions results from 'draft' to 'published'.
     * Once published, normal updates are blocked by the model layer.
     * Only a formal recalculate (Super Admin) can change published results.
     *
     * @throws ValidationException if results are already published or locked
     *
     * @return array{ affected_subject_results: int, affected_overall_results: int, status: string }
     */
    public function publish(int $campaignId): array
    {
        // Check current state — any 'draft' rows are eligible
        $draftSubjectResults = ExamResult::where('campaign_id', $campaignId)
            ->where('status', ReviewStatus::Draft->value)
            ->count();

        $draftOverallResults = ExamOverallResult::where('campaign_id', $campaignId)
            ->where('status', ReviewStatus::Draft->value)
            ->count();

        if ($draftSubjectResults === 0 && $draftOverallResults === 0) {
            // Check if already fully published/locked
            $anyPublished = ExamResult::where('campaign_id', $campaignId)
                ->whereIn('status', [ReviewStatus::Published->value, ReviewStatus::Locked->value])
                ->exists();

            if ($anyPublished) {
                throw ValidationException::withMessages([
                    'campaign_id' => "Exam results for campaign {$campaignId} are already published or locked.",
                ]);
            }

            // No results exist at all — nothing to publish
            throw ValidationException::withMessages([
                'campaign_id' => "No exam results found for campaign {$campaignId} to publish.",
            ]);
        }

        // Transition to published
        ExamResult::where('campaign_id', $campaignId)
            ->where('status', ReviewStatus::Draft->value)
            ->update(['status' => ReviewStatus::Published->value]);

        ExamOverallResult::where('campaign_id', $campaignId)
            ->where('status', ReviewStatus::Draft->value)
            ->update(['status' => ReviewStatus::Published->value]);

        // Write audit log
        AuditLoge::create([
            'user_id'    => auth()->id(),
            'entity'     => 'campaign_results',
            'entity_id'  => $campaignId,
            'action'     => 'publish',
            'diff'       => [
                'action'            => 'published',
                'campaign_id'       => $campaignId,
                'subject_results'   => $draftSubjectResults,
                'overall_results'   => $draftOverallResults,
            ],
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return [
            'affected_subject_results' => $draftSubjectResults,
            'affected_overall_results' => $draftOverallResults,
            'status'                   => ReviewStatus::Published->value,
        ];
    }

    /**
     * Audited recalculation for a campaign.
     *
     * Only callable by Super Admin (role_id = 1).
     * Before recalculation:
     * 1. Snapshot all current subject + overall results into result_snapshots
     * 2. Increment the version on existing rows
     * 3. Recalculate via ScoringServices
     * 4. Set status back to 'published'
     * 5. Write detailed audit log with old/new values, reason, who, when
     *
     * @param  int    $campaignId
     * @param  string $reason     Required explanation for the recalculation
     * @return array{
     *     campaign_id: int,
     *     version: int,
     *     candidates_recalculated: int,
     *     snapshots_created: int,
     *     reason: string,
     *     recalculated_by: array,
     * }
     *
     * @throws ValidationException
     */
    public function recalculate(int $campaignId, string $reason): array
    {
        $user = auth()->user();

        // --- Super Admin guard ---
        if (! $user || (int) $user->role_id !== 1) {
            throw ValidationException::withMessages([
                'role' => 'Only Super Admin can trigger a formal recalculation.',
            ]);
        }

        // --- Determine the next version from max across both tables ---
        $subjectMaxVersion = ExamResult::where('campaign_id', $campaignId)->max('version') ?? 1;
        $overallMaxVersion = ExamOverallResult::where('campaign_id', $campaignId)->max('version') ?? 1;
        $currentVersion = max($subjectMaxVersion, $overallMaxVersion);

        $newVersion = $currentVersion + 1;

        // ---------------------------------------------------------------
        //  1. Load & snapshot current results BEFORE recalculation
        // ---------------------------------------------------------------
        $now = now();

        // Single load for subject results — used for emptiness check AND old data
        $oldSubjectData = ExamResult::where('campaign_id', $campaignId)->get()->map(function ($r) {
            return [
                'candidate_id' => $r->candidate_id,
                'subject_id'   => $r->subject_id,
                'raw_score'    => (float) $r->raw_score,
                'final_score'  => (float) $r->final_score,
                'deduction'    => (float) $r->deduction,
                'passed'       => $r->passed,
                'status'       => $r->status->value ?? 'draft',
                'version'      => $r->version,
            ];
        });

        // Single load for overall results
        $oldOverallData = ExamOverallResult::where('campaign_id', $campaignId)->get()->map(function ($r) {
            return [
                'candidate_id'          => $r->candidate_id,
                'total_weighted_score'  => (float) $r->total_weighted_score,
                'overall_percentage'    => (float) $r->overall_percentage,
                'passed'                => $r->passed,
                'status'                => $r->status->value ?? 'draft',
                'version'               => $r->version,
            ];
        });

        if ($oldSubjectData->isEmpty() && $oldOverallData->isEmpty()) {
            throw ValidationException::withMessages([
                'campaign_id' => "No exam results exist for campaign {$campaignId} to recalculate.",
            ]);
        }

        // ---------------------------------------------------------------
        //  2. Perform the actual recalculation via ScoringServices
        // ---------------------------------------------------------------
        $scoringService = app(ScoringServices::class);
        $perCandidateResults = $scoringService->recalculateCampaign($campaignId);

        // ---------------------------------------------------------------
        //  3. Update version & status on all rows after recalculation
        // ---------------------------------------------------------------
        ExamResult::where('campaign_id', $campaignId)
            ->update([
                'version' => $newVersion,
                'status'  => ReviewStatus::Published->value,
            ]);

        ExamOverallResult::where('campaign_id', $campaignId)
            ->update([
                'version' => $newVersion,
                'status'  => ReviewStatus::Published->value,
            ]);

        // ---------------------------------------------------------------
        //  4. Build new_data for snapshots (post-recalculation)
        // ---------------------------------------------------------------
        $newSubjectData = ExamResult::where('campaign_id', $campaignId)->get()->map(function ($r) {
            return [
                'candidate_id' => $r->candidate_id,
                'subject_id'   => $r->subject_id,
                'raw_score'    => (float) $r->raw_score,
                'final_score'  => (float) $r->final_score,
                'deduction'    => (float) $r->deduction,
                'passed'       => $r->passed,
                'status'       => $r->status->value ?? 'published',
                'version'      => $r->version,
            ];
        });

        $newOverallData = ExamOverallResult::where('campaign_id', $campaignId)->get()->map(function ($r) {
            return [
                'candidate_id'          => $r->candidate_id,
                'total_weighted_score'  => (float) $r->total_weighted_score,
                'overall_percentage'    => (float) $r->overall_percentage,
                'passed'                => $r->passed,
                'status'                => $r->status->value ?? 'published',
                'version'               => $r->version,
            ];
        });

        // ---------------------------------------------------------------
        //  5. Insert snapshot records for each subject result
        // ---------------------------------------------------------------
        $snapshotsCreated = 0;

        foreach ($oldSubjectData as $old) {
            $candidateId = $old['candidate_id'];
            $subjectId   = $old['subject_id'];

            $new = $newSubjectData->firstWhere(
                fn ($n) => $n['candidate_id'] === $candidateId && $n['subject_id'] === $subjectId
            );

            ResultSnapshot::create([
                'campaign_id'      => $campaignId,
                'candidate_id'     => $candidateId,
                'subject_id'       => $subjectId,
                'old_data'         => $old,
                'new_data'         => $new ?? $old,
                'version'          => $newVersion,
                'reason'           => $reason,
                'recalculated_by'  => $user->id,
            ]);

            $snapshotsCreated++;
        }

        // Snapshot overall results too
        foreach ($oldOverallData as $old) {
            $candidateId = $old['candidate_id'];

            $new = $newOverallData->firstWhere(
                fn ($n) => $n['candidate_id'] === $candidateId
            );

            ResultSnapshot::create([
                'campaign_id'      => $campaignId,
                'candidate_id'     => $candidateId,
                'subject_id'       => null, // null = overall result
                'old_data'         => $old,
                'new_data'         => $new ?? $old,
                'version'          => $newVersion,
                'reason'           => $reason,
                'recalculated_by'  => $user->id,
            ]);

            $snapshotsCreated++;
        }

        // ---------------------------------------------------------------
        //  6. Write audit log entry — summary only; detailed per-row
        //     snapshots live in result_snapshots table.
        // ---------------------------------------------------------------
        AuditLoge::create([
            'user_id'    => $user->id,
            'entity'     => 'campaign_results',
            'entity_id'  => $campaignId,
            'action'     => 'recalculate',
            'diff'       => [
                'campaign_id'            => $campaignId,
                'old_version'            => $currentVersion,
                'new_version'            => $newVersion,
                'reason'                 => $reason,
                'snapshots_created'      => $snapshotsCreated,
                'candidates_recalculated' => count($perCandidateResults),
            ],
            'ip_address' => request()->ip(),
            'created_at' => $now,
        ]);

        return [
            'campaign_id'           => $campaignId,
            'version'               => $newVersion,
            'candidates_recalculated'=> count($perCandidateResults),
            'snapshots_created'     => $snapshotsCreated,
            'reason'                => $reason,
            'recalculated_by'       => [
                'id'   => $user->id,
                'name' => $user->name,
                'email'=> $user->email,
            ],
        ];
    }
}
