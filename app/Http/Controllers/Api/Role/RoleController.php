<?php

namespace App\Http\Controllers\Api\Role;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Role\StoreRoleRequest;
use App\Http\Requests\Api\Role\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Services\RoleServices;

class RoleController extends Controller
{
    public function __construct(protected RoleServices $roleService) {}

    public function index(): JsonResponse
    {
        $roles = $this->roleService->list(request()->all());

        return ApiResponse::success($roles, 'Roles retrieved successfully');
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return ApiResponse::created($role, 'Role created successfully');
    }

    public function show(Role $role): JsonResponse
    {
        return ApiResponse::success(
            $this->roleService->find($role),
            'Role retrieved successfully'
        );
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->update($role, $request->validated());

        return ApiResponse::success($role, 'Role updated successfully');
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->delete($role);

        return ApiResponse::ok('Role deleted successfully');
    }
}
