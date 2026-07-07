<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function profile()
    {
        return response()->json(auth()->user()->load('role'));
    }
}
