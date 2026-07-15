<?php

namespace App\Http\Controllers\Api\Province;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

use App\Http\Requests\Api\Province\StoreProvinceRequest;
use App\Http\Requests\Api\Province\UpdateProvinceRequest;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\ProvinceServices;

class ProvinceController extends Controller
{
    public function __construct(
        protected ProvinceServices $provinceService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $provinces = $this->provinceService->list($request->all());

        return ApiResponse::success(
            $provinces,
            'Provinces retrieved successfully'
        );
    }

    public function store(StoreProvinceRequest $request): JsonResponse
    {
        $province = $this->provinceService->create(
            $request->validated()
        );

        return ApiResponse::created(
            $province,
            'Province created successfully'
        );
    }

    public function show(Province $province): JsonResponse
    {
        return ApiResponse::success(
            $this->provinceService->find($province),
            'Province retrieved successfully'
        );
    }

    public function update(
        UpdateProvinceRequest $request,
        Province $province
    ): JsonResponse {
        $province = $this->provinceService->update(
            $province,
            $request->validated()
        );

        return ApiResponse::success(
            $province,
            'Province updated successfully'
        );
    }

    public function destroy(Province $province): JsonResponse
    {
        $this->provinceService->delete($province);

        return ApiResponse::ok(
            'Province deleted successfully'
        );
    }
}