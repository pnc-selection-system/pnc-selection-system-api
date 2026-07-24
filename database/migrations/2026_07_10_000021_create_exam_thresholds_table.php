<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration has been moved to 2026_07_10_000026_create_exam_thresholds_table.php
     * to fix ordering (needs to run AFTER 2026_07_10_000010_create_exam_subjects_table.php).
     * 
     * This stub is kept as a no-op to avoid breaking existing installations.
     */
    public function up(): void
    {
        // Drop if leftover from a previous failed migration attempt
        Schema::dropIfExists('exam_thresholds');

        Schema::create('exam_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('selection_campaigns')
                ->onDelete('cascade');
            $table->foreignId('subject_id')
                ->nullable()
                ->constrained('exam_subjects')
                ->onDelete('cascade');
            $table->decimal('overall_pass_mark', 6, 2)->nullable();
            $table->decimal('per_subject_min', 6, 2)->nullable();
            $table->boolean('must_pass_every_subject')->default(false);
            $table->timestamps();

            // One threshold per campaign+subject combo (subject_id=null = overall)
            $table->unique(['campaign_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_thresholds');
    }
};
