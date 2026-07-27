<?php

namespace Database\Seeders;

use App\Models\SelectCampaing;
use Illuminate\Database\Seeder;

class SelectionCampaignSeeder extends Seeder
{
    /**
     * Seed a default selection campaign if none exists.
     */
    public function run(): void
    {
        if (SelectCampaing::count() > 0) {
            $this->command->info('Selection campaigns already exist — skipping.');

            return;
        }

        SelectCampaing::create([
            'name' => 'PNC Selection 2026',
            'year' => 2026,
            'condidate_total' => 200,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'Active',
        ]);

        $this->command->info('Default selection campaign seeded successfully.');
    }
}
