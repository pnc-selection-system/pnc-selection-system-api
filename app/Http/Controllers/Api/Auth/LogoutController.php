<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Services\AuthServices;

class LogoutController extends Controller
{
    public function __construct(protected AuthServices $authService)
    {
    }

    public function logout(): JsonResponse
    {
        $result = $this->authService->logout();

        return ApiResponse::fromServiceResult($result);
    }
}
