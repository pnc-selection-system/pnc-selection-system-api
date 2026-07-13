<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Siem Reab'        => ['Phum Thmei', 'Phum Kandal', 'Phum Kraom', 'Phum Leu'],
            'Svay Dangkum'     => ['Phum Svay', 'Phum Dangkum', 'Phum Prey'],
            'Rattanak'         => ['Phum Rattanak', 'Phum Thmei', 'Phum Kbal'],
            'Tonle Bassac'     => ['Phum Bassac', 'Phum Toul', 'Phum Krang'],
            'Boeung Keng Kang' => ['BKK 1', 'BKK 2', 'BKK 3'],
            'Kampong Cham'     => ['Phum Cham', 'Phum Thmei', 'Phum Kandal'],
            'Kampong Bay'      => ['Phum Bay', 'Phum Thmei', 'Phum Kraom'],
            'Buon'             => ['Phum Buon', 'Phum Thmei', 'Phum Leu'],
            'Kratie'           => ['Phum Kratie', 'Phum Thmei', 'Phum Kandal'],
            'Pursat'           => ['Phum Pursat', 'Phum Thmei', 'Phum Kraom'],
        ];

        $communes = DB::table('communes')->pluck('id', 'name');

        foreach ($data as $communeName => $villages) {
            $communeId = $communes[$communeName] ?? null;
            if (!$communeId) continue;

            foreach ($villages as $village) {
                DB::table('villages')->insertOrIgnore([
                    'commune_id' => $communeId,
                    'name'       => $village,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
