<?php

namespace App\Console\Commands;

use App\Models\AssessmentRespone;
use App\Models\User;
use Illuminate\Console\Command;

class BackfillAssessmentSubmitters extends Command
{
    protected $signature = 'assessments:backfill-submitters';

    protected $description = 'Backfill submitted_by on old assessment responses with the first active user';

    public function handle(): int
    {
        $firstUser = User::where('active', true)->orderBy('id')->first();

        if (!$firstUser) {
            $this->error('No active users found. Please create a user first.');
            return Command::FAILURE;
        }

        $updated = AssessmentRespone::whereNull('submitted_by')
            ->update(['submitted_by' => $firstUser->id]);

        $this->info("Backfilled submitted_by for {$updated} assessment response(s) with user '{$firstUser->name}' (ID: {$firstUser->id}).");

        return Command::SUCCESS;
    }
}
