<?php

namespace Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function list(array $filters = []): Collection
    {
        $query = Role::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest('id')->get();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function find(Role $role): Role
    {
        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role;
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }
}
