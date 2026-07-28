<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        // Drop if leftover from a previous failed migration attempt
        Schema::dropIfExists('exam_thresholds');

        if (!Schema::hasTable('exam_subjects') || !Schema::hasTable('selection_campaigns') || Schema::hasTable('exam_thresholds')) {
            return;
        }


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
