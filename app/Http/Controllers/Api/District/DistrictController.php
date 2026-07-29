<?php

namespace App\Http\Controllers\Api\District;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\District;
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'province_id' => 'required|integer|exists:provinces,id',
            'name' => 'required|string|max:100',
        ]);
        $district = $this->districtService->create($request->only('province_id', 'name'));

        return ApiResponse::created($district, 'District created successfully');
    }

    public function show(District $district): JsonResponse
    {
        return ApiResponse::success(
            $this->districtService->find($district),
            'District retrieved successfully'
        );
    }

    public function update(Request $request, District $district): JsonResponse
    {
        $request->validate([
            'province_id' => 'sometimes|integer|exists:provinces,id',
            'name' => 'sometimes|string|max:100',
        ]);
        $district = $this->districtService->update($district, $request->only('province_id', 'name'));

        return ApiResponse::success($district, 'District updated successfully');
    }

    public function destroy(District $district): JsonResponse
    {
        $this->districtService->delete($district);

        return ApiResponse::ok('District deleted successfully');
    }
}
