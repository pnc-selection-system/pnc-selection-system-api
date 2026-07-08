<?php

namespace App\Http\Controllers\Api\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct(protected CampaignService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'year', 'status', 'start_date_from', 'start_date_to', 'end_date_from', 'end_date_to']);
        $perPage = $request->input('per_page', 15);
        
        $campaigns = $this->service->getPaginated($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Campaigns retrieved successfully.',
            'data' => $campaigns->items(),
            'pagination' => [
                'total' => $campaigns->total(),
                'per_page' => $campaigns->perPage(),
                'current_page' => $campaigns->currentPage(),
                'last_page' => $campaigns->lastPage(),
                'from' => $campaigns->firstItem(),
                'to' => $campaigns->lastItem(),
            ]
        ]);
    }

    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = $this->service->create($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully.',
            'data' => $campaign
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Campaign retrieved successfully.',
            'data' => $this->service->findById($id)
        ]);
    }

    public function update(UpdateCampaignRequest $request, int $id): JsonResponse
    {
        $campaign = $this->service->update($id, $request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Campaign updated successfully.',
            'data' => $campaign
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
