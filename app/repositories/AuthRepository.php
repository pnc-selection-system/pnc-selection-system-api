<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }
}