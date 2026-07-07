<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Http\Resources\Campaign\CampaignResource;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\JsonResponse;

class CampaignController extends Controller
{
    public function __construct(protected CampaignService $service) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Campaigns retrieved successfully.',
            'data' => CampaignResource::collection($this->service->getAll())
        ]);
    }

    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = $this->service->create($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully.',
            'data' => new CampaignResource($campaign)
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Campaign retrieved successfully.',
            'data' => new CampaignResource($this->service->findById($id))
        ]);
    }

    public function update(UpdateCampaignRequest $request, int $id): JsonResponse
    {
        $campaign = $this->service->update($id, $request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Campaign updated successfully.',
            'data' => new CampaignResource($campaign)
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully.'
        ], 200);
    }
}
