<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        // sample communes keyed by district name
        $data = [
            'Siem Reap'        => ['Siem Reab', 'Svay Dangkum', 'Sala Kamreuk', 'Kouk Chak', 'Nokor Thum'],
            'Battambang'       => ['Rattanak', 'Svay Por', 'Chheu Teal', 'Kdol', 'Chamkar Samraong'],
            'Phnom Penh'       => ['Tonle Bassac', 'Boeung Keng Kang', 'Toul Svay Prey', 'Toul Tom Poung', 'Olympic'],
            'Kampong Cham'     => ['Kampong Cham', 'Veal Vong', 'Boeng Kok', 'Kampong Reab', 'Prey Thnong'],
            'Kandal Stung'     => ['Kandal Stung', 'Prek Ambel', 'Prek Thmei', 'Samraong', 'Svay Chrum'],
            'Takhmao'          => ['Takhmao', 'Prek Roka', 'Prek Thmei', 'Samraong Knong', 'Svay Prey'],
            'Kampot'           => ['Kampong Bay', 'Andoung Khmer', 'Boeng Salat', 'Chambok', 'Chhnok Tru'],
            'Sihanoukville'    => ['Buon', 'Mittapheap', 'Pir', 'Prey Nob', 'Stueng Hav'],
            'Kratie'           => ['Kratie', 'Prek Patang', 'Sambour', 'Koh Trong', 'Chhloung'],
            'Pursat'           => ['Pursat', 'Phlov Meas', 'Kandieng', 'Bakan', 'Krakor'],
        ];

        $districts = DB::table('districts')->pluck('id', 'name');

        foreach ($data as $districtName => $communes) {
            $districtId = $districts[$districtName] ?? null;
            if (!$districtId) continue;

            foreach ($communes as $commune) {
                DB::table('communes')->insertOrIgnore([
                    'district_id' => $districtId,
                    'name'        => $commune,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
