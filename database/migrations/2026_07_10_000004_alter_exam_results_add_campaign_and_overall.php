<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ⚠️ This migration has been moved to 2026_07_10_000027_alter_exam_results_add_campaign_and_overall.php
     * to fix ordering issues (needs to run AFTER candidates and exam_results tables are created).
     * 
     * DO NOT add logic here — at this timestamp (000004), the referenced tables 
     * (candidates at 000007, exam_results at 000011) don't exist yet.
     */
    public function up(): void
    {
        // No-op — handled by 2026_07_10_000027_alter_exam_results_add_campaign_and_overall.php
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_overall_results');
    }
};
