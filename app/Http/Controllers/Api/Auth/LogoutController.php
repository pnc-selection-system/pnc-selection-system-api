<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
<<<<<<< HEAD
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
=======
use Illuminate\Http\JsonResponse;
use Services\AuthServices;
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc

class LogoutController extends Controller
{
    public function __construct(protected AuthServices $authService)
    {
    }

<<<<<<< HEAD
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
=======
    public function logout(): JsonResponse
    {
        $result = $this->authService->logout();

        return ApiResponse::fromServiceResult($result);
>>>>>>> 8b829ca40b1863c5165ba13b969ca533aef058cc
    }
}
