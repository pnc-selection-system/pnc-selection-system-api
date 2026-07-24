<?php

namespace App\Http\Controllers\Api\ExamResult;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Services\ResultsService;

class ResultsController extends Controller
{
    public function __construct(protected ResultsService $resultsService) {}

    /**
     * Get all rounds (campaigns) with exam results.
     * @GET /api/exam-results/rounds
     */
    public function rounds(): JsonResponse
    {
        try {
            $rounds = $this->resultsService->getRounds();
            return ApiResponse::success($rounds, 'Rounds retrieved successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Get provinces that have candidates with results.
     * @GET /api/exam-results/provinces?campaign_id=X
     */
    public function provinces(): JsonResponse
    {
        try {
            $campaignId = (int) request()->query('campaign_id', 0);
            $provinces = $this->resultsService->getProvinces($campaignId);
            return ApiResponse::success($provinces, 'Provinces retrieved successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Get summary statistics for a campaign.
     * @GET /api/exam-results/summary?campaign_id=X
     */
    public function summary(): JsonResponse
    {
        try {
            $campaignId = (int) request()->query('campaign_id', 0);
            $summary = $this->resultsService->getSummary($campaignId);
            return ApiResponse::success($summary, 'Summary retrieved successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Get score distribution for a campaign.
     * @GET /api/exam-results/distribution?campaign_id=X&province=All
     */
    public function distribution(): JsonResponse
    {
        try {
            $campaignId = (int) request()->query('campaign_id', 0);
            $province = request()->query('province', 'All provinces');
            $distribution = $this->resultsService->getScoreDistribution($campaignId, $province);
            return ApiResponse::success($distribution, 'Distribution retrieved successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Get candidate results table for a campaign.
     * @GET /api/exam-results/table?campaign_id=X
     */
    public function table(): JsonResponse
    {
        try {
            $campaignId = (int) request()->query('campaign_id', 0);
            $rows = $this->resultsService->getResultsTable($campaignId);
            return ApiResponse::success($rows, 'Results table retrieved successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
