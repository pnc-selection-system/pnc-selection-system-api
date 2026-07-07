<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
            public function login(Request $request)
        {
            $credentials = $request->only('email', 'password');

            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid email or password'
                    ], 401);
                }
            } catch (JWTException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not create token'
                ], 500);
            }

            $user = auth()->user();

            if (!$user->active) {
                JWTAuth::invalidate(JWTAuth::getToken());
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user'  => $user->load('role'),
                    'token' => $token,
                    'token_type' => 'bearer',
                ]
            ]);
        }
}
