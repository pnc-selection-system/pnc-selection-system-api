<?php

namespace App\Http\Controllers\Api\Exam;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Exam\PreviewExamConfigRequest;
use App\Http\Requests\Api\Exam\UpdateExamConfigRequest;
use Illuminate\Http\JsonResponse;
use Services\ExamConfigServices;

class ExamConfigController extends Controller
{
    public function __construct(protected ExamConfigServices $examConfigService) {}

    /**
     * GET /exam-config/{campaignId}
     * Returns campaign + subjects (with thresholds) + overall threshold.
     */
    public function show(int $campaignId): JsonResponse
    {
        try {
            $config = $this->examConfigService->getConfig($campaignId);

            return ApiResponse::success($config, 'Exam configuration retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound("Campaign with ID {$campaignId} not found.");
        }
    }

    /**
     * PUT /exam-config/{campaignId}
     * Batch upsert thresholds and return the full updated config.
     */
    public function update(UpdateExamConfigRequest $request, int $campaignId): JsonResponse
    {
        try {
            $config = $this->examConfigService->updateConfig($campaignId, $request->validated());

            return ApiResponse::success($config, 'Exam configuration updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound("Campaign with ID {$campaignId} not found.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError('Threshold validation failed', $e->errors());
        }
    }

    /**
     * POST /exam-config/{campaignId}/preview
     * Preview scoring impact for sample scores before saving thresholds.
     */
    public function preview(PreviewExamConfigRequest $request, int $campaignId): JsonResponse
    {
        try {
            $result = $this->examConfigService->previewScores($campaignId, $request->validated());

            return ApiResponse::success($result, 'Scoring preview generated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound("Campaign with ID {$campaignId} not found.");
        }
    }
}
