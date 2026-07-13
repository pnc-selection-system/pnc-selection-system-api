<?php

namespace App\Http\Controllers\Api\Exam;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Services\ExamResultServices;

class ExamResultController extends Controller
{
    public function __construct(protected ExamResultServices $examResultService) {}

    /**
     * GET /exam-results/{campaignId}/ranking
     *
     * Returns overall ranking (by overall_percentage) and per-subject rankings
     * (by final_score) for the given campaign.
     */
    public function ranking(int $campaignId): JsonResponse
    {
        try {
            $ranking = $this->examResultService->getRanking($campaignId);

            return ApiResponse::success($ranking, 'Ranking retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound("Campaign with ID {$campaignId} not found.");
        }
    }

    /**
     * POST /exam-results/{campaignId}/apply-thresholds
     *
     * Apply thresholds to auto-set candidate status to EXAM_PASSED or EXAM_FAILED.
     */
    public function applyThresholds(int $campaignId): JsonResponse
    {
        try {
            $result = $this->examResultService->applyThresholds($campaignId);

            return ApiResponse::success($result, 'Thresholds applied successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound("Campaign with ID {$campaignId} not found.");
        }
    }

    /**
     * GET /exam-results/{campaignId}/stats
     *
     * Returns campaign-level exam statistics: pass rate, average, by-province breakdown.
     */
    public function stats(int $campaignId): JsonResponse
    {
        try {
            $stats = $this->examResultService->getStats($campaignId);

            return ApiResponse::success($stats, 'Statistics retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound("Campaign with ID {$campaignId} not found.");
        }
    }

    /**
     * POST /exam-results/{campaignId}/publish
     *
     * Publish (lock) all exam results for a campaign.
     * Transitions from 'draft' to 'published'.
     * Once published, normal edits are blocked — only a formal recalculate
     * (Super Admin) can change results.
     */
    public function publish(int $campaignId): JsonResponse
    {
        try {
            $result = $this->examResultService->publish($campaignId);

            return ApiResponse::success($result, 'Exam results published successfully');
        } catch (ValidationException $e) {
            return ApiResponse::validationError('Publish failed', $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound("Campaign with ID {$campaignId} not found.");
        }
    }

    /**
     * POST /exam-results/{campaignId}/recalculate
     *
     * Audited recalculation. Restricted to Super Admin (role_id = 1).
     * Requires a 'reason' field in the request body.
     * Snapshots old results, recalculates, writes audit log with full diff.
     */
    public function recalculate(Request $request, int $campaignId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        try {
            $result = $this->examResultService->recalculate(
                $campaignId,
                $request->input('reason')
            );

            return ApiResponse::success($result, 'Exam results recalculated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::validationError('Recalculation failed', $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound("Campaign with ID {$campaignId} not found.");
        }
    }
}
