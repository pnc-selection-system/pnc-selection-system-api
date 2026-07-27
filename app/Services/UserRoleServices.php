<?php

namespace Services;

use App\Models\Permission;
use App\Models\Role;
use Repositories\UserRoleRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserRoleServices
{
    public function __construct(protected UserRoleRepository $repository) {}

    public function list(array $filters = [])
    {
        return $this->repository->list($filters);
    }

    public function getAllUsers()
    {
        return $this->repository->getAllUsers();
    }

    public function create(array $data)
    {
        $user = DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });

        // Only clear the cache after a successful creation
        $this->forgetPermissionCache();

        return $user;
    }

    public function deactivate(int $id)
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->deactivate($id);
        });
    }

    public function activate(int $id)
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->activate($id);
        });
    }

    public function roles()
    {
        return Role::select('id', 'name')->orderBy('name')->get();
    }

    /**
     * Return the permission matrix built from database records.
     *
     * Cached for 1 hour because the matrix only changes when an admin
     * modifies permission assignments (which clears the cache).
     *
     * Shape:
     *   rows: [{ capability: string, module: string, access: { [roleName]: bool } }]
     */
    public function permissionMatrix(): array
    {
        return Cache::remember('permission_matrix', 3600, function () {
            $roles = $this->roles();
            $roleNames = $roles->pluck('name')->toArray();

            $permissions = Permission::with('roles:id,name')
                ->orderBy('module')
                ->orderBy('name')
                ->get();

            $roleKeyMap = [
                'Admin'   => 'ADM',
                'Manager' => 'MGR',
                'Officer' => 'OFF',
            ];

            $rows = $permissions->map(function (Permission $perm) use ($roleNames, $roleKeyMap) {
                $access = [];
                foreach ($roleNames as $roleName) {
                    $key = $roleKeyMap[$roleName] ?? $roleName;
                    $role = $perm->roles->firstWhere('name', $roleName);
                    $access[$key] = $role !== null;
                }

                return [
                    'capability' => $perm->desc ?? $perm->name,
                    'name'       => $perm->name,
                    'module'     => $perm->module,
                    'access'     => $access,
                ];
            });

            return ['rows' => $rows->toArray()];
        });
    }

    /**
     * Forget the cached permission matrix so next request rebuilds it.
     */
    public function forgetPermissionCache(): void
    {
        Cache::forget('permission_matrix');
    }
}
