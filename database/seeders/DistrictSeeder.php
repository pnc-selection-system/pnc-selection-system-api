<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        // keyed by province name => list of districts
        $data = [
            'Banteay Meanchey' => ['Mongkol Borei', 'Phnum Srok', 'Preah Netr Preah', 'Serei Saophoan', 'Svay Chek', 'Thma Puok'],
            'Battambang'       => ['Banan', 'Battambang', 'Ek Phnom', 'Kamrieng', 'Moung Ruessei', 'Phnom Proek', 'Rotanak Mondol', 'Sampov Lun', 'Sangkae', 'Samlout', 'Thma Koul', 'Tichit', 'Rukhak Kiri'],
            'Kampong Cham'     => ['Batheay', 'Chamkar Leu', 'Cheung Prey', 'Dambae', 'Kampong Cham', 'Kampong Siem', 'Kang Meas', 'Koh Soutin', 'Prey Chhor', 'Srey Santhor', 'Stueng Trang'],
            'Kampong Chhnang'  => ['Baribour', 'Chol Kiri', 'Kampong Chhnang', 'Kampong Leaeng', 'Kampong Tralach', 'Kirivong', 'Rolea B\'ier', 'Sameakki Mean Chey', 'Tuek Phos'],
            'Kampong Speu'     => ['Aoral', 'Basedth', 'Chbar Mon', 'Kong Pisei', 'Oral', 'Phnom Sruoch', 'Samraong Tong', 'Thpong'],
            'Kampong Thom'     => ['Baray', 'Kampong Svay', 'Kampong Thom', 'Prasat Ballangk', 'Prasat Sambour', 'Sandann', 'Santuk', 'Stoung'],
            'Kampot'           => ['Angkor Chey', 'Banteay Meas', 'Chhouk', 'Dang Tong', 'Kampong Trach', 'Kampot', 'Toek Chhou'],
            'Kandal'           => ['Ang Snuol', 'Kandal Stung', 'Kien Svay', 'Khsach Kandal', 'Leuk Daek', 'Lvea Em', 'Muk Kampul', 'Ponhea Lueu', 'Rohas', 'S\'ang', 'Saart', 'Takhmao'],
            'Kep'              => ['Damnak Chang\'aeur', 'Kep'],
            'Koh Kong'         => ['Botum Sakor', 'Kiri Sakor', 'Koh Kong', 'Mondol Seima', 'Smach Mean Chey', 'Sre Ambel', 'Thma Bang'],
            'Kratie'           => ['Chhloung', 'Kratie', 'Prek Prasab', 'Sambour', 'Snuol'],
            'Mondulkiri'       => ['Kaev Seima', 'Koh Nhek', 'O Reang Au', 'Pech Chreada', 'Sen Monorom'],
            'Oddar Meanchey'   => ['Anlong Veng', 'Banteay Ampil', 'Chong Kal', 'Samraong', 'Trapeang Prasat'],
            'Pailin'           => ['Pailin', 'Sala Krau'],
            'Phnom Penh'       => ['Chamkar Mon', 'Chbar Ampov', 'Chroy Changvar', 'Dangkao', 'Don Penh', 'Mean Chey', 'Por Senchey', 'Praek Pnov', 'Prampir Meakkakra', 'Russey Keo', 'Saensokh', 'Sen Sok', 'Tuol Kouk'],
            'Preah Sihanouk'   => ['Kampong Seila', 'Prey Nob', 'Sihanoukville', 'Stueng Hav'],
            'Preah Vihear'     => ['Chey Saen', 'Chhaeb', 'Choam Ksan', 'Kulen', 'Rovieng', 'Sangkom Thmei', 'Tbeng Meanchey'],
            'Prey Veng'        => ['Ba Phnum', 'Kamchay Mear', 'Kampong Leav', 'Kampong Trabek', 'Kanhchriech', 'Me Sang', 'Peam Chor', 'Peam Ro', 'Pea Reang', 'Prey Veng', 'Preah Sdach', 'Sithor Kandal', 'Svay Antor'],
            'Pursat'           => ['Bakan', 'Kandieng', 'Krakor', 'Phnom Kravanh', 'Pursat', 'Veal Veng'],
            'Ratanakiri'       => ['Andong Meas', 'Ban Lung', 'Bar Kaev', 'Koun Mom', 'Lumphat', 'O Chum', 'O Ya Dav', 'Ou Chum', 'Veun Sai'],
            'Siem Reap'        => ['Angkor Chum', 'Angkor Thom', 'Banteay Srei', 'Chi Kraeng', 'Kralanh', 'Prasat Bakong', 'Puok', 'Siem Reap', 'Sotr Nikum', 'Srei Snam', 'Svay Leu', 'Varin'],
            'Stung Treng'      => ['Sesan', 'Siem Bouk', 'Siem Pang', 'Stung Treng', 'Thala Barivat'],
            'Svay Rieng'       => ['Chantrea', 'Kampong Rou', 'Romeas Haek', 'Svay Chrum', 'Svay Rieng', 'Svay Teab'],
            'Takeo'            => ['Angkor Borei', 'Bati', 'Borei Cholsar', 'Doun Kaev', 'Kiri Vong', 'Kirivong', 'Prey Kabbas', 'Samraong', 'Tram Kak', 'Treang'],
            'Tboung Khmum'     => ['Dambae', 'Krouch Chhmar', 'Memot', 'Ou Reang Ov', 'Ponhea Kraek', 'Tboung Khmum'],
        ];

        $provinces = DB::table('provinces')->pluck('id', 'name');

        foreach ($data as $provinceName => $districts) {
            $provinceId = $provinces[$provinceName] ?? null;
            if (!$provinceId) continue;

            foreach ($districts as $district) {
                DB::table('districts')->insertOrIgnore([
                    'province_id' => $provinceId,
                    'name'        => $district,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
