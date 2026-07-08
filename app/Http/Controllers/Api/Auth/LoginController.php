<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\StoreLoginRequest;
use Illuminate\Http\JsonResponse;
use Services\AuthServices;

class LoginController extends Controller
{
    public function __construct(protected AuthServices $authService)
    {
    }

    public function login(StoreLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->only('email', 'password'));

        return ApiResponse::fromServiceResult($result);
    }
}
