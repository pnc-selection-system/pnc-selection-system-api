<?php

namespace Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Repositories\AuthRepository;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthServices
{
    public function __construct(protected AuthRepository $authRepository)
    {
    }

    public function login(array $credentials): array
    {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password',
                    'status'  => 401,
                ];
            }
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'Could not create token',
                'status'  => 500,
            ];
        }

        $user = Auth::user()->load('role:id,name');

        if (!$user->active) {
            JWTAuth::invalidate(JWTAuth::getToken());

            return [
                'success' => false,
                'message' => 'Your account is inactive',
                'status'  => 403,
            ];
        }

        return [
            'success' => true,
            'message' => 'Login successful',
            'status'  => 200,
            'data'    => [
                'user'         => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role?->name ?? 'User',
                ],
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'expires_in'   => config('jwt.ttl') * 60,
            ],
        ];
    }

    public function register(array $data): array
    {
        $user = $this->authRepository->create([
            'role_id'  => $data['role_id'],
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'phone'    => $data['phone'] ?? null,
            'active'   => true,
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'success' => true,
            'message' => 'User registered successfully',
            'status'  => 201,
            'data'    => [
                'user'         => $user->load('role'),
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'expires_in'   => config('jwt.ttl') * 60,
            ],
        ];
    }

    public function logout(): array
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Token not found',
                    'status'  => 401,
                ];
            }

            JWTAuth::invalidate($token);

            return [
                'success' => true,
                'message' => 'Successfully logged out',
                'status'  => 200,
            ];
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'Failed to logout. Token may be expired or invalid.',
                'status'  => 500,
            ];
        }
    }

    public function profile(): array
    {
        $user = Auth::user()->load('role');

        return [
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'status'  => 200,
            'data'    => $user,
        ];
    }
}
