<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function profile()
    {
        $user = auth()->user()->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data'    => $user,
        ]);
    }
}
