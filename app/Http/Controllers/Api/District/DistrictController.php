<?php

namespace App\Http\Controllers\Api\District;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\DistrictServices;

class DistrictController extends Controller
{
    public function __construct(protected DistrictServices $districtService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $districts = $this->districtService->list($request->only(['province_id']));

        return ApiResponse::success($districts, 'Districts retrieved successfully');
    }
}
