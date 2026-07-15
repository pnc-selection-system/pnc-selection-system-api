<?php

namespace App\Http\Controllers\Api\NgoPartner;

use App\Helpers\ApiResponse;
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

        return ApiResponse::success($ngoPartners, 'NGO partners retrieved successfully');
    }

    public function store(StoreNgoPartnerRequest $request): JsonResponse
    {
        $ngoPartner = $this->ngoPartnerService->create($request->validated());

        return ApiResponse::created($ngoPartner, 'NGO partner created successfully');
    }

    public function show(NgoPartner $ngoPartner): JsonResponse
    {
        return ApiResponse::success(
            $this->ngoPartnerService->find($ngoPartner),
            'NGO partner retrieved successfully'
        );
    }

    public function update(UpdateNgoPartnerRequest $request, NgoPartner $ngoPartner): JsonResponse
    {
        $ngoPartner = $this->ngoPartnerService->update($ngoPartner, $request->validated());

        return ApiResponse::success($ngoPartner, 'NGO partner updated successfully');
    }

    public function destroy(NgoPartner $ngoPartner): JsonResponse
    {
        $this->ngoPartnerService->delete($ngoPartner);

        return ApiResponse::ok('NGO partner deleted successfully');
    }

    public function candidates(NgoPartner $ngoPartner): JsonResponse
    {
        $candidates = $this->ngoPartnerService->candidates($ngoPartner->id, request()->all());

        return ApiResponse::success($candidates, 'Candidates retrieved successfully');
    }
}
