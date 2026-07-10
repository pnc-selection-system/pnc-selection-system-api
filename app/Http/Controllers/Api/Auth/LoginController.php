<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Services\AuthServices;

class LoginController extends Controller
{
    public function __construct(protected AuthServices $authServices)
    {
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $result = $this->authServices->login($credentials);

        return ApiResponse::fromServiceResult($result);
    }
}
