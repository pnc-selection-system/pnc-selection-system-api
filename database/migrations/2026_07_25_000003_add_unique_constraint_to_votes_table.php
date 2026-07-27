<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('votes', function (Blueprint $table) {
            // Drop any potential duplicates before adding unique constraint
            // Keep the most recent vote record per (round, candidate, member)
            $duplicates = DB::table('votes')
                ->select('voting_round_id', 'candidate_id', 'member_id', DB::raw('MAX(id) as max_id'))
                ->groupBy('voting_round_id', 'candidate_id', 'member_id')
                ->having(DB::raw('COUNT(*)'), '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                DB::table('votes')
                    ->where('voting_round_id', $dup->voting_round_id)
                    ->where('candidate_id', $dup->candidate_id)
                    ->where('member_id', $dup->member_id)
                    ->where('id', '<>', $dup->max_id)
                    ->delete();
            }

            $table->unique(['voting_round_id', 'candidate_id', 'member_id'], 'votes_round_candidate_member_unique');
        });
    }

    public function down()
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique('votes_round_candidate_member_unique');
        });
    }
};
