<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutController extends Controller
{
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if (! $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found',
                ], 401);
            }

            JWTAuth::invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout. Token may be expired or invalid.',
            ], 500);
        }
    }
}
