<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class VillageSeeder extends Seeder
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
        $insertBuffer = [];
        $batchSize = 500; // Batching inserts to prevent memory overflows
        $totalCount = 0;

        foreach ($provinces as $province) {
            $provinceId = $province['id']; // e.g. "phnom_penh", "battambang"

            $this->command->info("Fetching village data for {$province['english']}...");

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

                // Find the parent district ID from your local database
                $districtRow = DB::table('districts')->where('name', $districtName)->first();
                if (!$districtRow) {
                    $this->command->warn("District '{$districtName}' not found in local DB, skipping its villages...");
                    continue;
                }

                foreach ($district['communes'] as $commune) {
                    $communeName = $commune['english'] ?? null;
                    if (!$communeName || !isset($commune['villages'])) {
                        continue;
                    }

                    // Find the parent commune ID by name AND district_id (handles duplicate commune names across districts)
                    $communeRow = DB::table('communes')
                        ->where('name', $communeName)
                        ->where('district_id', $districtRow->id)
                        ->first();

                    if (!$communeRow) {
                        $this->command->warn("Commune '{$communeName}' not found in local DB, skipping its villages...");
                        continue;
                    }

                    foreach ($commune['villages'] as $village) {
                        $villageName = $village['english'] ?? null;
                        if (!$villageName) {
                            continue;
                        }

                        $insertBuffer[] = [
                            'name'        => trim($villageName),
                            'commune_id'  => $communeRow->id,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ];

                        // When buffer reaches batch size, insert and flush memory
                        if (count($insertBuffer) >= $batchSize) {
                            DB::table('villages')->insertOrIgnore($insertBuffer);
                            $totalCount += count($insertBuffer);
                            $insertBuffer = [];
                        }
                    }
                }
            }
        }

        // Insert remaining records left in the buffer
        if (!empty($insertBuffer)) {
            DB::table('villages')->insertOrIgnore($insertBuffer);
            $totalCount += count($insertBuffer);
        }

        $this->command->info("Successfully seeded {$totalCount} villages across Cambodia!");
    }
}
