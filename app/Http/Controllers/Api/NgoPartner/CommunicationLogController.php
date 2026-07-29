<?php

namespace App\Http\Controllers\Api\NgoPartner;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NgoPartner\StoreCommunicationLogRequest;
use App\Models\NgoPartner;
use Illuminate\Http\JsonResponse;
use Services\NgoPartnerServices;

class CommunicationLogController extends Controller
{
    public function __construct(protected NgoPartnerServices $ngoPartnerService) {}

    public function index(NgoPartner $ngoPartner): JsonResponse
    {
        $logs = $this->ngoPartnerService->listCommunicationLogs($ngoPartner->id);

        return ApiResponse::success($logs, 'Communication logs retrieved successfully');
    }

    public function store(StoreCommunicationLogRequest $request, NgoPartner $ngoPartner): JsonResponse
    {
        $log = $this->ngoPartnerService->createCommunicationLog($ngoPartner->id, $request->validated());

        return ApiResponse::created($log, 'Communication log created successfully');
    }
}
