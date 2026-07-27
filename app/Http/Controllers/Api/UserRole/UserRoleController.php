<?php

namespace App\Http\Controllers\Api\UserRole;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserRole\StoreUserRoleRequest;
use Services\UserRoleServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function __construct(protected UserRoleServices $service) {}

    /**
     * List users with their roles.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->service->list($request->only(['search', 'per_page']));

        return ApiResponse::success($users, 'Users retrieved successfully');
    }

    /**
     * Get all users without pagination.
     */
    public function allUsers(): JsonResponse
    {
        $users = $this->service->getAllUsers();

        return ApiResponse::success($users, 'Users retrieved successfully');
    }

    /**
     * Create a new user.
     */
    public function store(StoreUserRoleRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());

        return ApiResponse::created($user->load('role:id,name'), 'User created successfully');
    }

    /**
     * Deactivate a user.
     */
    public function deactivate(int $id): JsonResponse
    {
        $user = $this->service->deactivate($id);

        return ApiResponse::success($user, 'User deactivated successfully');
    }

    /**
     * Activate a user.
     */
    public function activate(int $id): JsonResponse
    {
        $user = $this->service->activate($id);

        return ApiResponse::success($user, 'User activated successfully');
    }

    /**
     * List available roles for dropdowns.
     */
    public function roles(): JsonResponse
    {
        $roles = $this->service->roles();

        return ApiResponse::success($roles, 'Roles retrieved successfully');
    }

    /**
     * Get permission matrix.
     */
    public function permissionMatrix(): JsonResponse
    {
        $matrix = $this->service->permissionMatrix();

        return ApiResponse::success($matrix, 'Permission matrix retrieved successfully');
    }
}
