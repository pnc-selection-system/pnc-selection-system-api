<?php

namespace Database\Seeders;

use App\Enums\VotingMethod;
use App\Enums\VotingRoundStatus;
use App\Models\SelectCampaing;
use App\Models\VotingRound;
use Illuminate\Database\Seeder;

class VotingRoundSeeder extends Seeder
{
    /**
     * Seed a default voting round if none exists.
     * Requires at least one selection campaign to exist.
     */
    public function run(): void
    {
        if (VotingRound::count() > 0) {
            $this->command->info('Voting rounds already exist — skipping.');

            return;
        }

        $campaign = SelectCampaing::first();

        if (! $campaign) {
            $this->command->error('No selection campaign found. Run SelectionCampaignSeeder first.');

            return;
        }

        VotingRound::create([
            'campaign_id' => $campaign->id,
            'name' => 'Round 1',
            'voting_method' => VotingMethod::Majority,
            'status' => VotingRoundStatus::Open,
            'quorum' => 3,
            'pass_threshold' => 50,
            'waitlist_cap' => 5,
            'total_members' => 7,
        ]);

        $this->command->info('Default voting round seeded successfully with ID=1.');
    }
}
