<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Services\AuthServices;

class RefreshController extends Controller
{
    public function __construct(protected AuthServices $authService) {}

    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $result = $this->authService->refresh($request->input('refresh_token'));

        return ApiResponse::fromServiceResult($result);
    }
}
