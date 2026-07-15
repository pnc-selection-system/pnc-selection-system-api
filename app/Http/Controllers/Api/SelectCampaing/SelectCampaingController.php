<?php

namespace App\Http\Controllers\Api\SelectCampaing;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SelectCampaing\StoreSelectCampaingRequest;
use App\Http\Requests\Api\SelectCampaing\UpdateSelectCampaingRequest;
use App\Models\SelectCampaing;
use Illuminate\Http\JsonResponse;
use Services\SelectCampaingServices;

class SelectCampaingController extends Controller
{
    public function __construct(protected SelectCampaingServices $selectCampaingService)
    {
    }

    public function index(): JsonResponse
    {
        $selectCampaings = $this->selectCampaingService->list(request()->only(['status', 'year', 'per_page']));

        return ApiResponse::success($selectCampaings, 'Selection campaigns retrieved successfully');
    }

    public function store(StoreSelectCampaingRequest $request): JsonResponse
    {
        $selectCampaing = $this->selectCampaingService->create($request->validated());

        return ApiResponse::created($selectCampaing, 'Selection campaign created successfully');
    }

    public function show(SelectCampaing $selectCampaing): JsonResponse
    {
        return ApiResponse::success(
            $this->selectCampaingService->find($selectCampaing),
            'Selection campaign retrieved successfully'
        );
    }

    public function update(UpdateSelectCampaingRequest $request, SelectCampaing $selectCampaing): JsonResponse
    {
        $selectCampaing = $this->selectCampaingService->update($selectCampaing, $request->validated());

        return ApiResponse::success($selectCampaing, 'Selection campaign updated successfully');
    }

    public function destroy(SelectCampaing $selectCampaing): JsonResponse
    {
        $this->selectCampaingService->delete($selectCampaing);

        return ApiResponse::ok('Selection campaign deleted successfully');
    }
}
