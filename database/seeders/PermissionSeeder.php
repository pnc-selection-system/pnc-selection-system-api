<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── 1. Define all permissions grouped by module ─────────────────
        $permissions = [
            // Users & Roles
            ['name' => 'users.view',        'module' => 'Users',        'desc' => 'View users list'],
            ['name' => 'users.create',      'module' => 'Users',        'desc' => 'Create new users'],
            ['name' => 'users.edit',        'module' => 'Users',        'desc' => 'Edit existing users'],
            ['name' => 'users.deactivate',  'module' => 'Users',        'desc' => 'Deactivate / activate users'],
            ['name' => 'roles.manage',      'module' => 'Users',        'desc' => 'Manage roles & permissions'],

            // Campaign
            ['name' => 'campaigns.view',    'module' => 'Campaign',     'desc' => 'View campaigns'],
            ['name' => 'campaigns.create',  'module' => 'Campaign',     'desc' => 'Create campaigns'],
            ['name' => 'campaigns.edit',    'module' => 'Campaign',     'desc' => 'Edit campaigns'],
            ['name' => 'campaigns.delete',  'module' => 'Campaign',     'desc' => 'Delete campaigns'],

            // Info Sessions
            ['name' => 'sessions.view',     'module' => 'Info Session', 'desc' => 'View info sessions'],
            ['name' => 'sessions.create',   'module' => 'Info Session', 'desc' => 'Create info sessions'],
            ['name' => 'sessions.edit',     'module' => 'Info Session', 'desc' => 'Edit info sessions'],
            ['name' => 'sessions.delete',   'module' => 'Info Session', 'desc' => 'Delete info sessions'],

            // NGO Partners
            ['name' => 'ngos.view',         'module' => 'NGO Partner',  'desc' => 'View NGO partners'],
            ['name' => 'ngos.create',       'module' => 'NGO Partner',  'desc' => 'Create NGO partners'],
            ['name' => 'ngos.edit',         'module' => 'NGO Partner',  'desc' => 'Edit NGO partners'],
            ['name' => 'ngos.delete',       'module' => 'NGO Partner',  'desc' => 'Delete NGO partners'],

            // Candidates
            ['name' => 'candidates.view',   'module' => 'Candidate',    'desc' => 'View candidates'],
            ['name' => 'candidates.create', 'module' => 'Candidate',    'desc' => 'Create candidates'],
            ['name' => 'candidates.edit',   'module' => 'Candidate',    'desc' => 'Edit candidates'],
            ['name' => 'candidates.delete', 'module' => 'Candidate',    'desc' => 'Delete candidates'],
            ['name' => 'candidates.import', 'module' => 'Candidate',    'desc' => 'Import candidates from file'],

            // Exam
            ['name' => 'exam.view',         'module' => 'Exam',         'desc' => 'View exams / subjects'],
            ['name' => 'exam.configure',    'module' => 'Exam',         'desc' => 'Configure exam thresholds & subjects'],
            ['name' => 'exam.import',       'module' => 'Exam',         'desc' => 'Import exam results'],
            ['name' => 'exam.results',      'module' => 'Exam',         'desc' => 'View / publish exam results'],

            // Assessment
            ['name' => 'assessment.view',   'module' => 'Assessment',   'desc' => 'View assessments & forms'],
            ['name' => 'assessment.manage', 'module' => 'Assessment',   'desc' => 'Manage assessment forms & responses'],

            // Home Investigation
            ['name' => 'homeinv.view',      'module' => 'Home Investigation', 'desc' => 'View home investigations'],
            ['name' => 'homeinv.manage',    'module' => 'Home Investigation', 'desc' => 'Manage home investigations'],

            // Voting
            ['name' => 'voting.view',       'module' => 'Voting',       'desc' => 'View voting rounds & votes'],
            ['name' => 'voting.cast',       'module' => 'Voting',       'desc' => 'Cast votes'],
            ['name' => 'voting.manage',     'module' => 'Voting',       'desc' => 'Manage voting rounds'],

            // Reports
            ['name' => 'reports.view',      'module' => 'Reports',      'desc' => 'View reports & analytics'],
            ['name' => 'reports.export',    'module' => 'Reports',      'desc' => 'Export reports'],
        ];

        // ── 2. Upsert permissions into the database ────────────────────
        $createdIds = [];
        foreach ($permissions as $perm) {
            $model = Permission::updateOrCreate(
                ['name' => $perm['name']],
                $perm
            );
            $createdIds[$perm['name']] = $model->id;
        }

        $this->command->info('Permissions seeded: ' . count($permissions));

        // ── 3. Assign permissions to roles ─────────────────────────────
        $admin   = Role::where('name', 'Admin')->first();
        $manager = Role::where('name', 'Manager')->first();
        $officer = Role::where('name', 'Officer')->first();

        if (! $admin || ! $manager || ! $officer) {
            $this->command->warn('Roles not found — run RoleSeeder first. Skipping role-permission assignments.');
            return;
        }

        // ── 4. Assign permissions to roles based on permission matrix ─────
        // Permission Matrix:
        // ADM (Admin): Manage users ✓, Configure exam ✓, Edit candidate ✓, Publish results ✓, Cast vote ✗
        // MGR (Manager): Manage users ✗, Configure exam ✓, Edit candidate ✓, Publish results ✓, Cast vote ✗
        // OFF (Officer): Manage users ✗, Configure exam ✗, Edit candidate ✓, Publish results ✗, Cast vote ✗

        // ---- Admin: Manage users, Configure exam, Edit candidate, Publish results ----
        $adminPerms = [
            'users.view', 'users.create', 'users.edit', 'users.deactivate', 'roles.manage',
            'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.delete',
            'sessions.view', 'sessions.create', 'sessions.edit', 'sessions.delete',
            'ngos.view', 'ngos.create', 'ngos.edit', 'ngos.delete',
            'candidates.view', 'candidates.create', 'candidates.edit', 'candidates.delete', 'candidates.import',
            'exam.view', 'exam.configure', 'exam.import', 'exam.results',
            'assessment.view', 'assessment.manage',
            'homeinv.view', 'homeinv.manage',
            'voting.view', 'voting.manage',
            'reports.view', 'reports.export',
        ];
        $admin->permissions()->sync(
            array_intersect_key($createdIds, array_flip($adminPerms))
        );

        // ---- Manager: Configure exam, Edit candidate, Publish results ----
        $managerPerms = [
            'campaigns.view', 'campaigns.create', 'campaigns.edit',
            'sessions.view', 'sessions.create', 'sessions.edit',
            'ngos.view', 'ngos.create', 'ngos.edit',
            'candidates.view', 'candidates.create', 'candidates.edit', 'candidates.import',
            'exam.view', 'exam.configure', 'exam.import', 'exam.results',
            'assessment.view', 'assessment.manage',
            'homeinv.view', 'homeinv.manage',
            'voting.view',
            'reports.view', 'reports.export',
        ];
        $manager->permissions()->sync(
            array_intersect_key($createdIds, array_flip($managerPerms))
        );

        // ---- Officer: Edit candidate only ----
        $officerPerms = [
            'campaigns.view',
            'sessions.view',
            'candidates.view', 'candidates.create', 'candidates.edit', 'candidates.import',
            'exam.view',
            'assessment.view',
            'homeinv.view',
            'voting.view',
        ];
        $officer->permissions()->sync(
            array_intersect_key($createdIds, array_flip($officerPerms))
        );

        $this->command->info('Permissions assigned to roles successfully!');
    }
}
