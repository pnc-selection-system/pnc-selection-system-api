<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fetching province list from cambodia-gazetteer...');

        // Fetch the list of all provinces from the NorakGithub gazetteer
        $response = Http::withoutVerifying()->get(
            'https://raw.githubusercontent.com/NorakGithub/cambodia-gazetteer/main/provinces.json'
        );

        if ($response->failed()) {
            $this->command->error('Failed to fetch province list from cambodia-gazetteer. Please check your network connection.');
            return;
        }

        $provinces = $response->json();
        $totalCommunes = 0;

        foreach ($provinces as $province) {
            $provinceId = $province['id']; // e.g. "phnom_penh", "battambang"

            $this->command->info("Fetching commune data for {$province['english']}...");

            $detailResponse = Http::withoutVerifying()->get(
                "https://raw.githubusercontent.com/NorakGithub/cambodia-gazetteer/main/provinces/{$provinceId}.json"
            );

            if ($detailResponse->failed()) {
                $this->command->warn("Could not fetch details for {$province['english']}, skipping...");
                continue;
            }

            $districts = $detailResponse->json();

            foreach ($districts as $district) {
                $districtName = $district['english'] ?? null;
                if (!$districtName || !isset($district['communes'])) {
                    continue;
                }

                // Look up the district by its English name in your local database
                $districtRow = DB::table('districts')->where('name', $districtName)->first();

                if (!$districtRow) {
                    $this->command->warn("District '{$districtName}' not found in local DB (names may differ), skipping...");
                    continue;
                }

                foreach ($district['communes'] as $commune) {
                    $communeName = $commune['english'] ?? null;
                    if (!$communeName) {
                        continue;
                    }

                    DB::table('communes')->updateOrInsert(
                        [
                            'name'        => trim($communeName),
                            'district_id' => $districtRow->id,
                        ],
                        [
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]
                    );
                    $totalCommunes++;
                }
            }
        }

        $this->command->info("Successfully seeded {$totalCommunes} communes!");
    }
}
