<?php

namespace Services;

use App\Models\Role;
use Repositories\RoleRepository;

class RoleServices
{
    public function __construct(protected RoleRepository $roleRepository) {}

    public function list(array $filters = [])
    {
        return $this->roleRepository->list($filters);
    }

    public function create(array $data): Role
    {
        return $this->roleRepository->create($data);
    }

    public function find(Role $role): Role
    {
        return $this->roleRepository->find($role);
    }

    public function update(Role $role, array $data): Role
    {
        return $this->roleRepository->update($role, $data);
    }

    public function delete(Role $role): void
    {
        $this->roleRepository->delete($role);
    }
}
