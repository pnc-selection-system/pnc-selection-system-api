<?php

namespace App\Http\Controllers\Api\Campaign;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Models\SelectCampaing;
use Illuminate\Http\JsonResponse;
use Services\SelectCampaingServices;

class CampaignController extends Controller
{
    public function __construct(protected SelectCampaingServices $campaignService) {}

    public function index(): JsonResponse
    {
        $campaigns = $this->campaignService->list(request()->all());

        return ApiResponse::success($campaigns, 'Campaigns retrieved successfully');
    }

    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = $this->campaignService->create($request->validated());

        return ApiResponse::created($campaign, 'Campaign created successfully');
    }

    public function show(SelectCampaing $campaign): JsonResponse
    {
        return ApiResponse::success(
            $this->campaignService->find($campaign),
            'Campaign retrieved successfully'
        );
    }

    public function update(UpdateCampaignRequest $request, SelectCampaing $campaign): JsonResponse
    {
        $campaign = $this->campaignService->update($campaign, $request->validated());

        return ApiResponse::success($campaign, 'Campaign updated successfully');
    }

    public function destroy(SelectCampaing $campaign): JsonResponse
    {
        $this->campaignService->delete($campaign);

        return ApiResponse::ok('Campaign deleted successfully');
    }
}
