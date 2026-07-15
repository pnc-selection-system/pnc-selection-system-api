<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {

        $adminRole = Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);
        $managerRole = Role::firstOrCreate(['id' => 2], ['name' => 'Manager']);
        $officerRole = Role::firstOrCreate(['id' => 3], ['name' => 'Officer']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'phone' => '0123456789',
                'active' => true,
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'role_id' => $managerRole->id,
                'name' => 'Manager User',
                'password' => Hash::make('manager123'),
                'phone' => '0123456790',
                'active' => true,
            ]
        );

        $officer = User::firstOrCreate(
            ['email' => 'officer@gmail.com'],
            [
                'role_id' => $officerRole->id,
                'name' => 'Officer User',
                'password' => Hash::make('officer123'),
                'phone' => '0123456791',
                'active' => true,
            ]
        );

        $adminToken = JWTAuth::fromUser($admin);
        $managerToken = JWTAuth::fromUser($manager);
        $officerToken = JWTAuth::fromUser($officer);

        $this->command->info('👑 ADMIN');
        $this->command->info('Email    : admin@gmail.com');
        $this->command->info('Password : admin123');
        $this->command->info('Token    : '.$adminToken);

        $this->command->newLine();

        $this->command->info('👔 MANAGER');
        $this->command->info('Email    : manager@gmail.com');
        $this->command->info('Password : manager123');
        $this->command->info('Token    : '.$managerToken);

        $this->command->newLine();

        $this->command->info('🛡️ OFFICER');
        $this->command->info('Email    : officer@gmail.com');
        $this->command->info('Password : officer123');
        $this->command->info('Token    : '.$officerToken);

    }
}
