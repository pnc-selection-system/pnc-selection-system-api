<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add campaign_id to exam_results for campaign-level querying
        Schema::table('exam_results', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                ->after('id')
                ->constrained('selection_campaigns')
                ->onDelete('cascade');
        });

        // Overall scores per candidate per campaign
        Schema::create('exam_overall_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('selection_campaigns')->onDelete('cascade');
            $table->decimal('total_weighted_score', 8, 2)->default(0);
            $table->decimal('overall_percentage', 6, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamps();

            $table->unique(['candidate_id', 'campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_overall_results');

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });
    }
};
