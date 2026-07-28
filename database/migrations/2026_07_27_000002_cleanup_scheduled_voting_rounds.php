<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update any existing rows that have 'Scheduled' status to 'Open'
        // since the 'Scheduled' case was removed from VotingRoundStatus enum
        DB::table('voting_rounds')
            ->where('status', 'Scheduled')
            ->update(['status' => 'Open']);
    }

    public function down(): void
    {
        // No rollback needed - this is a data cleanup
    }
};
