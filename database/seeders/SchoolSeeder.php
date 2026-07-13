<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['province' => 'Siem Reap',   'district' => 'Siem Reap',    'name' => 'Preah Ang Makhak Vi High School'],
            ['province' => 'Siem Reap',   'district' => 'Siem Reap',    'name' => 'Hun Sen Siem Reap High School'],
            ['province' => 'Battambang',  'district' => 'Battambang',   'name' => 'Preah Monivong High School'],
            ['province' => 'Battambang',  'district' => 'Battambang',   'name' => 'Hun Sen Battambang High School'],
            ['province' => 'Phnom Penh',  'district' => 'Chamkar Mon',  'name' => 'Sisowath High School'],
            ['province' => 'Phnom Penh',  'district' => 'Tuol Kouk',    'name' => 'Preah Sisowath High School'],
            ['province' => 'Kampong Cham','district' => 'Kampong Cham', 'name' => 'Hun Sen Kampong Cham High School'],
            ['province' => 'Kandal',      'district' => 'Takhmao',      'name' => 'Takhmao High School'],
            ['province' => 'Kampot',      'district' => 'Kampot',       'name' => 'Kampot High School'],
            ['province' => 'Preah Sihanouk', 'district' => 'Sihanoukville', 'name' => 'Sihanoukville High School'],
        ];

        $provinces = DB::table('provinces')->pluck('id', 'name');
        $districts  = DB::table('districts')->pluck('id', 'name');

        foreach ($data as $row) {
            DB::table('schools')->insertOrIgnore([
                'province_id' => $provinces[$row['province']] ?? null,
                'district_id' => $districts[$row['district']] ?? null,
                'name'        => $row['name'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
