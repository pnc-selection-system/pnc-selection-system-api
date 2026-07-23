<?php

namespace App\Http\Controllers\Api\Village;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Village;
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'commune_id' => 'required|integer|exists:communes,id',
            'name' => 'required|string|max:100',
        ]);
        $village = $this->villageService->create($request->only('commune_id', 'name'));

        return ApiResponse::created($village, 'Village created successfully');
    }

    public function show(Village $village): JsonResponse
    {
        return ApiResponse::success(
            $this->villageService->find($village),
            'Village retrieved successfully'
        );
    }

    public function update(Request $request, Village $village): JsonResponse
    {
        $request->validate([
            'commune_id' => 'sometimes|integer|exists:communes,id',
            'name' => 'sometimes|string|max:100',
        ]);
        $village = $this->villageService->update($village, $request->only('commune_id', 'name'));

        return ApiResponse::success($village, 'Village updated successfully');
    }

    public function destroy(Village $village): JsonResponse
    {
        $this->villageService->delete($village);

        return ApiResponse::ok('Village deleted successfully');
    }
}
