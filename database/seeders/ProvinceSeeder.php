<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            'Banteay Meanchey', 'Battambang', 'Kampong Cham', 'Kampong Chhnang',
            'Kampong Speu', 'Kampong Thom', 'Kampot', 'Kandal', 'Kep',
            'Koh Kong', 'Kratie', 'Mondulkiri', 'Oddar Meanchey', 'Pailin',
            'Phnom Penh', 'Preah Sihanouk', 'Preah Vihear', 'Prey Veng',
            'Pursat', 'Ratanakiri', 'Siem Reap', 'Stung Treng', 'Svay Rieng',
            'Takeo', 'Tboung Khmum',
        ];

        foreach ($provinces as $name) {
            DB::table('provinces')->insertOrIgnore([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
