<?php

namespace App\Http\Controllers\Api\Village;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\VillageServices;

class VillageController extends Controller
{
    public function __construct(protected VillageServices $villageService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $villages = $this->villageService->list($request->only(['commune_id', 'district_id', 'province_id']));

        return ApiResponse::success($villages, 'Villages retrieved successfully');
    }
}
