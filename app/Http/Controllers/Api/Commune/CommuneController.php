<?php

namespace App\Http\Controllers\Api\Commune;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Commune;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\CommuneServices;

class CommuneController extends Controller
{
    public function __construct(protected CommuneServices $communeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $communes = $this->communeService->list($request->only(['district_id']));

        return ApiResponse::success($communes, 'Communes retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'district_id' => 'required|integer|exists:districts,id',
            'name' => 'required|string|max:100',
        ]);
        $commune = $this->communeService->create($request->only('district_id', 'name'));

        return ApiResponse::created($commune, 'Commune created successfully');
    }

    public function show(Commune $commune): JsonResponse
    {
        return ApiResponse::success(
            $this->communeService->find($commune),
            'Commune retrieved successfully'
        );
    }

    public function update(Request $request, Commune $commune): JsonResponse
    {
        $request->validate([
            'district_id' => 'sometimes|integer|exists:districts,id',
            'name' => 'sometimes|string|max:100',
        ]);
        $commune = $this->communeService->update($commune, $request->only('district_id', 'name'));

        return ApiResponse::success($commune, 'Commune updated successfully');
    }

    public function destroy(Commune $commune): JsonResponse
    {
        $this->communeService->delete($commune);

        return ApiResponse::ok('Commune deleted successfully');
    }
}
