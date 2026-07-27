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

    private function roleKeyMap(): array
    {
        return [
            'Admin'   => 'ADM',
            'Manager' => 'MGR',
            'Officer' => 'OFF',
        ];
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
     *   rows: [{ capability, name, module, access: { ADM: bool, MGR: bool, OFF: bool } }]
     *   roles: [{ id, name, key }]
     */
    public function permissionMatrix(): array
    {
        return Cache::remember('permission_matrix', 3600, function () {
            $roles = $this->roles();
            $roleNames = $roles->pluck('name')->toArray();
            $roleKeyMap = $this->roleKeyMap();

            $permissions = Permission::with('roles:id,name')
                ->orderBy('module')
                ->orderBy('name')
                ->get();

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

            $roleDefs = $roles->map(fn($r) => [
                'id'   => $r->id,
                'name' => $r->name,
                'key'  => $roleKeyMap[$r->name] ?? $r->name,
            ])->values()->toArray();

            return [
                'rows'  => $rows->toArray(),
                'roles' => $roleDefs,
            ];
        });
    }

    /** Toggle a single permission assignment for a role. */
    public function togglePermission(string $permissionName, string $roleName, bool $granted): void
    {
        $permission = Permission::where('name', $permissionName)->firstOrFail();
        $role       = Role::where('name', $roleName)->firstOrFail();

        if ($granted) {
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        } else {
            $role->permissions()->detach($permission->id);
        }

        $this->forgetPermissionCache();
    }

    /**
     * Forget the cached permission matrix so next request rebuilds it.
     */
    public function forgetPermissionCache(): void
    {
        Cache::forget('permission_matrix');
    }
}
