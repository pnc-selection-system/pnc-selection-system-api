<?php

namespace App\Http\Controllers\Api\Province;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\ProvinceServices;

class ProvinceController extends Controller
{
    public function __construct(protected ProvinceServices $provinceService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $provinces = $this->provinceService->list($request->all());

        return ApiResponse::success($provinces, 'Provinces retrieved successfully');
    }
}