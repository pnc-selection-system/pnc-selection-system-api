<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            'Phnom Penh',
            'Banteay Meanchey',
            'Battambang',
            'Kampong Cham',
            'Kampong Chhnang',
            'Kampong Speu',
            'Kampong Thom',
            'Kampot',
            'Kandal',
            'Koh Kong',
            'Kratie',
            'Mondulkiri',
            'Preah Vihear',
            'Prey Veng',
            'Pursat',
            'Ratanakiri',
            'Siem Reap',
            'Preah Sihanouk',
            'Stung Treng',
            'Svay Rieng',
            'Takeo',
            'Oddar Meanchey',
            'Kep',
            'Pailin',
            'Tboung Khmum',
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->updateOrInsert(
                ['name' => $province], // Assuming your column name is 'name'
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
