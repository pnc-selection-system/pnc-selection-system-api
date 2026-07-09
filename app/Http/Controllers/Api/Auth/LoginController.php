<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
<<<<<<< HEAD
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token',
            ], 500);
        }

        $user = auth()->user();
        if (! $user->active) {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? 'User',
                ],
                'token_type' => 'Bearer',
                'access_token' => $token,
            ],
        ]);
=======
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
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }
}
