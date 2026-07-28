<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Permission definitions grouped by module.
     *
     * Each entry: [ 'name' => 'permission.key', 'desc' => 'Display description', 'module' => 'module' ]
     */
    private array $permissionDefs = [
        // ── Users ───────────────────────────────────────────────────────────
        ['name' => 'users.view',       'desc' => 'View users',               'module' => 'users'],
        ['name' => 'users.create',     'desc' => 'Create users',             'module' => 'users'],
        ['name' => 'users.deactivate', 'desc' => 'Deactivate / reactivate users', 'module' => 'users'],

        // ── Roles & Permissions ─────────────────────────────────────────────
        ['name' => 'roles.manage',     'desc' => 'Manage roles & permissions', 'module' => 'roles'],

        // ── Exams ───────────────────────────────────────────────────────────
        ['name' => 'exam.view',        'desc' => 'View exams',               'module' => 'exam'],
        ['name' => 'exam.configure',   'desc' => 'Configure exams',          'module' => 'exam'],

        // ── NGO Partners ────────────────────────────────────────────────────
        ['name' => 'ngos.view',        'desc' => 'View NGO partners',        'module' => 'ngos'],
        ['name' => 'ngos.create',      'desc' => 'Create NGO partners',      'module' => 'ngos'],
        ['name' => 'ngos.edit',        'desc' => 'Edit NGO partners',        'module' => 'ngos'],
        ['name' => 'ngos.delete',      'desc' => 'Delete NGO partners',      'module' => 'ngos'],

        // ── Candidates ──────────────────────────────────────────────────────
        ['name' => 'candidates.view',   'desc' => 'View candidates',         'module' => 'candidates'],
        ['name' => 'candidates.import', 'desc' => 'Import candidates',       'module' => 'candidates'],
        ['name' => 'candidates.manage', 'desc' => 'Manage candidate data',   'module' => 'candidates'],

        // ── Campaigns (Selection Campaigns) ─────────────────────────────────
        ['name' => 'campaigns.view',   'desc' => 'View campaigns',           'module' => 'campaigns'],
        ['name' => 'campaigns.manage', 'desc' => 'Create / edit campaigns',  'module' => 'campaigns'],

        // ── Assessments ─────────────────────────────────────────────────────
        ['name' => 'assessment.view',  'desc' => 'View assessments',         'module' => 'assessment'],
        ['name' => 'assessment.manage','desc' => 'Configure & run assessments','module' => 'assessment'],

        // ── Home Investigation ───────────────────────────────────────────────
        ['name' => 'homeinv.view',     'desc' => 'View home investigations', 'module' => 'homeinv'],
        ['name' => 'homeinv.conduct',  'desc' => 'Conduct investigations',   'module' => 'homeinv'],
        ['name' => 'homeinv.approve',  'desc' => 'Approve / reject results', 'module' => 'homeinv'],

        // ── Voting & Selection ───────────────────────────────────────────────
        ['name' => 'voting.view',      'desc' => 'View voting rounds',       'module' => 'voting'],
        ['name' => 'voting.manage',    'desc' => 'Create / manage rounds',   'module' => 'voting'],
        ['name' => 'voting.cast',      'desc' => 'Cast votes',              'module' => 'voting'],

        // ── Info Sessions ────────────────────────────────────────────────────
        ['name' => 'sessions.view',    'desc' => 'View info sessions',       'module' => 'sessions'],
        ['name' => 'sessions.manage',  'desc' => 'Manage info sessions',     'module' => 'sessions'],

        // ── Schools ──────────────────────────────────────────────────────────
        ['name' => 'schools.view',     'desc' => 'View schools',             'module' => 'schools'],
        ['name' => 'schools.manage',   'desc' => 'Manage schools',           'module' => 'schools'],

        // ── Reports ──────────────────────────────────────────────────────────
        ['name' => 'reports.view',     'desc' => 'View reports & exports',   'module' => 'reports'],
        ['name' => 'reports.export',   'desc' => 'Export data',             'module' => 'reports'],
    ];

    /**
     * Role definitions and which permission names each role gets.
     */
    private array $roleDefs = [
        [
            'name'        => 'Admin',
            'permissions' => [
                'users.view', 'users.create', 'users.deactivate',
                'roles.manage',
                'exam.view', 'exam.configure',
                'ngos.view', 'ngos.create', 'ngos.edit', 'ngos.delete',
                'candidates.view', 'candidates.import', 'candidates.manage',
                'campaigns.view', 'campaigns.manage',
                'assessment.view', 'assessment.manage',
                'homeinv.view', 'homeinv.conduct', 'homeinv.approve',
                'voting.view', 'voting.manage', 'voting.cast',
                'sessions.view', 'sessions.manage',
                'schools.view', 'schools.manage',
                'reports.view', 'reports.export',
            ],
        ],
        [
            'name'        => 'Manager',
            'permissions' => [
                'users.view',
                'roles.manage',
                'exam.view', 'exam.configure',
                'ngos.view', 'ngos.create', 'ngos.edit',
                'candidates.view', 'candidates.import',
                'campaigns.view', 'campaigns.manage',
                'assessment.view', 'assessment.manage',
                'homeinv.view', 'homeinv.conduct',
                'voting.view', 'voting.manage',
                'sessions.view', 'sessions.manage',
                'schools.view',
                'reports.view',
            ],
        ],
        [
            'name'        => 'Officer',
            'permissions' => [
                'exam.view',
                'ngos.view',
                'candidates.view',
                'campaigns.view',
                'assessment.view',
                'homeinv.view', 'homeinv.conduct',
                'voting.view', 'voting.cast',
                'sessions.view',
                'reports.view',
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          $this->call([
            ProvinceSeeder::class,
            DistrictSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
        ]);
        // ── 1. Create / update permissions ──────────────────────────────────
        $permissionMap = []; // name → id
        foreach ($this->permissionDefs as $def) {
            $perm = Permission::updateOrCreate(
                ['name' => $def['name']],
                ['desc' => $def['desc'], 'module' => $def['module']],
            );
            $permissionMap[$perm->name] = $perm->id;
        }

        $this->command->info('✓ Permissions seeded (' . count($permissionMap) . ' total)');

        // ── 2. Create / update roles and attach permissions ─────────────────
        foreach ($this->roleDefs as $def) {
            $role = Role::firstOrCreate(
                ['name' => $def['name']]
            );

            // Collect permission IDs for this role
            $permIds = [];
            foreach ($def['permissions'] as $permName) {
                if (isset($permissionMap[$permName])) {
                    $permIds[] = $permissionMap[$permName];
                } else {
                    $this->command->warn("  ⚠ Permission '{$permName}' not found — skipping for role '{$def['name']}'");
                }
            }

            // Sync the pivot table (replace existing assignments)
            $role->permissions()->sync($permIds);

            $this->command->info("  ✓ Role '{$def['name']}' — " . count($permIds) . ' permissions attached');
        }

        // ── 3. Ensure every existing user has a valid role_id ───────────────
        $adminRoleId = Role::where('name', 'Admin')->value('id');
        if ($adminRoleId) {
            $updated = User::whereNull('role_id')->orWhere('role_id', 0)->update(['role_id' => $adminRoleId]);
            if ($updated > 0) {
                $this->command->info("  ✓ {$updated} user(s) without a role were assigned to 'Admin'");
            }
        }

        // ── 4. Clear cached permission matrix ────────────────────────────────
        Cache::forget('permission_matrix');
        $this->command->info('✓ Permission matrix cache cleared');
    }
}
