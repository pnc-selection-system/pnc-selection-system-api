<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\StoreRegisterRequest;
use Illuminate\Http\JsonResponse;
use Services\AuthServices;

class RegisterController extends Controller
{
    public function __construct(protected AuthServices $authService)
    {
    }

    public function register(StoreRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::fromServiceResult($result);
    }
}
