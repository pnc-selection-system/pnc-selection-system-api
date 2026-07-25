<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Pivot table linking voting rounds to candidates
        if (! Schema::hasTable('voting_round_candidates')) {
            Schema::create('voting_round_candidates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('voting_round_id')->constrained('voting_rounds')->onDelete('cascade');
                $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['voting_round_id', 'candidate_id']);
            });
        }

        // Add locked_at and quorum/threshold columns to voting_rounds if they don't exist
        Schema::table('voting_rounds', function (Blueprint $table) {
            if (! Schema::hasColumn('voting_rounds', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('voting_rounds', 'quorum')) {
                $table->unsignedInteger('quorum')->default(0)->after('voting_method');
            }
            if (! Schema::hasColumn('voting_rounds', 'pass_threshold')) {
                $table->unsignedInteger('pass_threshold')->default(50)->after('quorum');
            }
            if (! Schema::hasColumn('voting_rounds', 'waitlist_cap')) {
                $table->unsignedInteger('waitlist_cap')->default(0)->after('pass_threshold');
            }
            if (! Schema::hasColumn('voting_rounds', 'total_members')) {
                $table->unsignedInteger('total_members')->default(0)->after('waitlist_cap');
            }
        });
    }

    public function down()
    {
        Schema::table('voting_rounds', function (Blueprint $table) {
            $table->dropColumn(['locked_at', 'quorum', 'pass_threshold', 'waitlist_cap', 'total_members']);
        });

        Schema::dropIfExists('voting_round_candidates');
    }
};
