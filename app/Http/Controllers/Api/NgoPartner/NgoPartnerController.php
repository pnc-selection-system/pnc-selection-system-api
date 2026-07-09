<?php

namespace App\Http\Controllers\Api\NgoPartner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NgoPartner\StoreNgoPartnerRequest;
use App\Http\Requests\Api\NgoPartner\UpdateNgoPartnerRequest;
use App\Models\NgoPartner;
use Illuminate\Http\JsonResponse;
use Services\NgoPartnerServices;

class NgoPartnerController extends Controller
{
    public function __construct(protected NgoPartnerServices $ngoPartnerService) {}

    public function index(): JsonResponse
    {
        $ngoPartners = $this->ngoPartnerService->list(request()->all());

        return response()->json([
            'success' => true,
            'message' => 'NGO partners retrieved successfully',
            'data' => $ngoPartners,
        ]);
    }

    public function store(StoreNgoPartnerRequest $request): JsonResponse
    {
        $ngoPartner = $this->ngoPartnerService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'NGO partner created successfully',
            'data' => $ngoPartner,
        ], 201);
    }

    public function show(NgoPartner $ngoPartner): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'NGO partner retrieved successfully',
            'data' => $this->ngoPartnerService->find($ngoPartner),
        ]);
    }

    public function update(UpdateNgoPartnerRequest $request, NgoPartner $ngoPartner): JsonResponse
    {
        $ngoPartner = $this->ngoPartnerService->update($ngoPartner, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'NGO partner updated successfully',
            'data' => $ngoPartner,
        ]);
    }

    public function destroy(NgoPartner $ngoPartner): JsonResponse
    {
        $this->ngoPartnerService->delete($ngoPartner);

        return response()->json([
            'success' => true,
            'message' => 'NGO partner deleted successfully',
        ]);
    }
}
