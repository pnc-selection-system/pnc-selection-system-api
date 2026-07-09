<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Services\AuthServices;

class ProfileController extends Controller
{
    public function __construct(protected AuthServices $authService)
    {
    }

    public function profile(): JsonResponse
    {
        $result = $this->authService->profile();

        return ApiResponse::fromServiceResult($result);
    }
}
