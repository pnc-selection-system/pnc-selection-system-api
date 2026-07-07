<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roles = [
            [
                'id'   => 1,
                'name' => 'Admin',
            ],
            [
                'id'   => 2,
                'name' => 'Manager',
            ],
            [
                'id'   => 3,
                'name' => 'Officer',
            ],
           
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']],
                $role
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}
