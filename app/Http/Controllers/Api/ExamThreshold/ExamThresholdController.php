<?php

namespace App\Http\Controllers\Api\ExamThreshold;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExamThreshold\StoreExamThresholdRequest;
use Illuminate\Http\JsonResponse;
use Services\ExamThresholdService;

class ExamThresholdController extends Controller
{
    public function __construct(protected ExamThresholdService $examThresholdService)
    {
    }

    /**
     * Get thresholds for a campaign
     */
    public function index(int $campaignId): JsonResponse
    {
        $thresholds = $this->examThresholdService->getCampaignThresholds($campaignId);

        return ApiResponse::success($thresholds, 'Exam thresholds retrieved successfully');
    }

    /**
     * Store or update overall threshold
     */
    public function storeOverall(StoreExamThresholdRequest $request, int $campaignId): JsonResponse
    {
        $data = $request->validated();
        $errors = $this->examThresholdService->validateThresholdData($data);

        if (!empty($errors)) {
            return ApiResponse::validationError($errors);
        }

        $threshold = $this->examThresholdService->upsertOverallThreshold($campaignId, $data);

        return ApiResponse::success($threshold, 'Overall threshold saved successfully');
    }

    /**
     * Store or update subject threshold
     */
    public function storeSubject(StoreExamThresholdRequest $request, int $campaignId, int $subjectId): JsonResponse
    {
        $data = $request->validated();
        $errors = $this->examThresholdService->validateThresholdData($data, $subjectId);

        if (!empty($errors)) {
            return ApiResponse::validationError($errors);
        }

        $threshold = $this->examThresholdService->upsertSubjectThreshold($campaignId, $subjectId, $data);

        return ApiResponse::success($threshold, 'Subject threshold saved successfully');
    }

    /**
     * Delete threshold
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->examThresholdService->deleteThreshold($id);

        if (!$deleted) {
            return ApiResponse::notFound('Threshold not found');
        }

        return ApiResponse::ok('Threshold deleted successfully');
    }
}
