<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Phnom Penh' => [
                'Chamkar Mon', 'Daun Penh', 'Prampi Makara', 'Tuol Kouk',
                'Dangkao', 'Meanchey', 'Russey Keo', 'Sen Sok', 'Pou Senchey',
                'Chroy Changvar', 'Prek Pnov', 'Chbar Ampov', 'Boeng Keng Kang',
                'Kamboul'
            ],
            'Banteay Meanchey' => [
                'Mongkol Borei', 'Phnum Srok', 'Preah Netr Preah', 'Ou Chrov',
                'Serei Saophoan', 'Thmar Pouk', 'Svay Chek', 'Malai', 'Poipet'
            ],
            'Battambang' => [
                'Banan', 'Thma Koul', 'Battambang', 'Bavel', 'Ekam Phnum',
                'Moung Ruessei', 'Rotanak Mondol', 'Sangkae', 'Samlout',
                'Sampov Loun', 'Phnum Protrek', 'Kamrieng', 'Koas Krala',
                'Rukh Kiri'
            ],
            'Kampong Cham' => [
                'Batheay', 'Chamkar Leu', 'Cheung Prey', 'Kampong Cham',
                'Kampong Siem', 'Kang Meas', 'Koh Sotin', 'Prey Chhor',
                'Srey Santhor', 'Stueng Trang'
            ],
            'Kampong Chhnang' => [
                'Baribour', 'Chol Kiri', 'Kampong Chhnang', 'Kampong Leaeng',
                'Kampong Tralach', 'Rolea B\'ier', 'Sameakki Mean Chey', 'Tuek Phos'
            ],
            'Kampong Speu' => [
                'Chbar Mon', 'Kong Pisei', 'Aoral', 'Oudong', 'Phnum Sruoch',
                'Samraong Tong', 'Thpong', 'Basedth'
            ],
            'Kampong Thom' => [
                'Baray', 'Kampong Svay', 'Stueng Saen', 'Prasat Balangk',
                'Prasat Sambour', 'Sandal', 'Santuk', 'Stoung', 'Taing Kouk'
            ],
            'Kampot' => [
                'Angkor Chey', 'Banteay Meas', 'Chhouk', 'Chum Kiri',
                'Dang Tong', 'Kampong Trach', 'Tuek Chhou', 'Kampot'
            ],
            'Kandal' => [
                'Kandal Stueng', 'Kien Svay', 'Khsach Kandal', 'Koh Thom',
                'Leuk Daek', 'Lvea Aem', 'Mukh Kampuul', 'Angk Snuol',
                'Ponhea Lueu', 'Sangkhum Thmei', 'Ta Khmau'
            ],
            'Koh Kong' => [
                'Botum Sakor', 'Kiri Sakor', 'Koh Kong', 'Smach Mean Chey',
                'Mondol Seima', 'Srae Ambel', 'Thma Baing'
            ],
            'Kratie' => [
                'Chhloung', 'Kratie', 'Prek Prasab', 'Sambour', 'Snuol', 'Chitr Bourei'
            ],
            'Mondulkiri' => [
                'Kaev Seima', 'Koh Nhek', 'O Reang', 'Pech Chreada', 'Sen Monorom'
            ],
            'Preah Vihear' => [
                'Chey Saen', 'Chhaeb', 'Choam Ksant', 'Kulen', 'Rovieng',
                'Sangkhum Thmei', 'Tvaeng', 'Preah Vihear'
            ],
            'Prey Veng' => [
                'Ba Phnum', 'Kamchay Mear', 'Kampong Trabaek', 'Kanhchriech',
                'Me Sang', 'Peam Chor', 'Peam Ro', 'Pea Reang', 'Preh Sdach',
                'Prey Veng', 'Pur Rieng', 'Sithor Kandal', 'Svay Antor'
            ],
            'Pursat' => [
                'Bakan', 'Kandieng', 'Krakor', 'Phnum Kravanh', 'Pursat', 'Veal Veang'
            ],
            'Ratanakiri' => [
                'Andoung Meas', 'Ban Lung', 'Bar Kaev', 'Koun Mom', 'Lumphat',
                'Ou Chum', 'Ou Ya Dav', 'Ta Vaeng', 'Veun Sai'
            ],
            'Siem Reap' => [
                'Angkor Chum', 'Angkor Thum', 'Banteay Srei', 'Chi Kraeng',
                'Kralanh', 'Puok', 'Prasat Bakong', 'Siem Reap', 'Soutr Nikum',
                'Srei Snam', 'Svay Leu', 'Varin'
            ],
            'Preah Sihanouk' => [
                'Preah Sihanouk', 'Stueng Hav', 'Prey Nob', 'Kampong Seila', 'Koh Rong'
            ],
            'Stung Treng' => [
                'Sesan', 'Siem Bouk', 'Siem Pang', 'Stung Treng', 'Thala Barivat'
            ],
            'Svay Rieng' => [
                'Chanthrea', 'Kampong Rou', 'Romduol', 'Romeas Haek',
                'Svay Chrum', 'Svay Rieng', 'Svay Teap', 'Bavet'
            ],
            'Takeo' => [
                'Angkor Borei', 'Bati', 'Bourei Cholsar', 'Kiri Vong',
                'Koh Andaet', 'Prey Kabbas', 'Samraong', 'Doun Kaev',
                'Tram Kak', 'Treang'
            ],
            'Oddar Meanchey' => [
                'Anlong Veng', 'Banteay Ampil', 'Chong Kal', 'Samraong', 'Trapeang Prasat'
            ],
            'Kep' => [
                'Damnak Chang\'aeur', 'Kep'
            ],
            'Pailin' => [
                'Pailin', 'Sala Krau'
            ],
            'Tboung Khmum' => [
                'Dambe', 'Krouch Chhmar', 'Memot', 'Ou Reang Ov',
                'Ponhea Kraek', 'Tboung Khmum', 'Suong'
            ]
        ];

        foreach ($data as $provinceName => $districts) {
            $provinceId = DB::table('provinces')->where('name', $provinceName)->value('id');

            if ($provinceId) {
                foreach ($districts as $districtName) {
                    DB::table('districts')->updateOrInsert(
                        [
                            'name'        => $districtName,
                            'province_id' => $provinceId
                        ],
                        [
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]
                    );
                }
            }
        }
    }
}
