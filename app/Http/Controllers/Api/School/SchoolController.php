<?php

namespace App\Http\Controllers\Api\School;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\School\StoreSchoolRequest;
use App\Http\Requests\Api\School\UpdateSchoolRequest;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Services\SchoolServices;

class SchoolController extends Controller
{
    public function __construct(protected SchoolServices $schoolService) {}

    public function index(): JsonResponse
    {
        $schools = $this->schoolService->list(request()->all());

        return ApiResponse::success($schools, 'Schools retrieved successfully');
    }

    public function store(StoreSchoolRequest $request): JsonResponse
    {
        $school = $this->schoolService->create($request->validated());

        return ApiResponse::created($school, 'School created successfully');
    }

    public function show(School $school): JsonResponse
    {
        return ApiResponse::success(
            $this->schoolService->find($school),
            'School retrieved successfully'
        );
    }

    public function update(UpdateSchoolRequest $request, School $school): JsonResponse
    {
        $school = $this->schoolService->update($school, $request->validated());

        return ApiResponse::success($school, 'School updated successfully');
    }

    public function destroy(School $school): JsonResponse
    {
        $this->schoolService->delete($school);

        return ApiResponse::ok('School deleted successfully');
    }
}
