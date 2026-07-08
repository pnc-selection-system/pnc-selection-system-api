<?php

namespace App\Http\Controllers\Api\SelectCampaing;

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
        $selectCampaings = $this->selectCampaingService->list(request()->all());

        return response()->json([
            'success' => true,
            'message' => 'Selection campaigns retrieved successfully',
            'data'    => $selectCampaings,
        ]);
    }

    public function store(StoreSelectCampaingRequest $request): JsonResponse
    {
        $selectCampaing = $this->selectCampaingService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Selection campaign created successfully',
            'data'    => $selectCampaing,
        ], 201);
    }

    public function show(SelectCampaing $selectCampaing): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Selection campaign retrieved successfully',
            'data'    => $this->selectCampaingService->find($selectCampaing),
        ]);
    }

    public function update(UpdateSelectCampaingRequest $request, SelectCampaing $selectCampaing): JsonResponse
    {
        $selectCampaing = $this->selectCampaingService->update($selectCampaing, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Selection campaign updated successfully',
            'data'    => $selectCampaing,
        ]);
    }

    public function destroy(SelectCampaing $selectCampaing): JsonResponse
    {
        $this->selectCampaingService->delete($selectCampaing);

        return response()->json([
            'success' => true,
            'message' => 'Selection campaign deleted successfully',
        ]);
    }
}
