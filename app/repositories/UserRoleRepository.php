<?php

namespace Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRoleRepository
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return User::with('role:id,name')
            ->when(! empty($filters['search']), fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('name', 'like', '%' . $v . '%')
                  ->orWhere('email', 'like', '%' . $v . '%');
            }))
            ->orderBy('id', 'desc')
            ->paginate($filters['per_page'] ?? 50);
    }

    public function create(array $data): User
    {
        return User::create([
            'role_id'  => $data['role_id'],
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'phone'    => $data['phone'] ?? null,
            'active'   => true,
        ]);
    }

    public function find(int $id): User
    {
        return User::with('role:id,name')->findOrFail($id);
    }

    public function deactivate(int $id): User
    {
        $user = User::findOrFail($id);
        $user->update(['active' => false]);

        return $user->fresh()->load('role:id,name');
    }

    public function activate(int $id): User
    {
        $user = User::findOrFail($id);
        $user->update(['active' => true]);

        return $user->fresh()->load('role:id,name');
    }

    public function getAllUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('role:id,name')
            ->orderBy('id', 'desc')
            ->get();
    }
}
