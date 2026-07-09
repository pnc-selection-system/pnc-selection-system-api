<?php

namespace App\Http\Controllers\Api\Province;

use App\Http\Controllers\Controller;
use App\Services\Province\ProvinceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    public function __construct(protected ProvinceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);
        
        $provinces = $this->service->getPaginated($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'message' => 'Provinces retrieved successfully.',
            'data' => $provinces->items(),
            'pagination' => [
                'total' => $provinces->total(),
                'per_page' => $provinces->perPage(),
                'current_page' => $provinces->currentPage(),
                'last_page' => $provinces->lastPage(),
                'from' => $provinces->firstItem(),
                'to' => $provinces->lastItem(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:provinces,name'
        ]);

        $province = $this->service->create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Province created successfully.',
            'data' => $province
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Province retrieved successfully.',
            'data' => $this->service->findById($id)
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:provinces,name,' . $id
        ]);

        $province = $this->service->update($id, $request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Province updated successfully.',
            'data' => $province
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Province deleted successfully.'
        ], 200);
    }
}