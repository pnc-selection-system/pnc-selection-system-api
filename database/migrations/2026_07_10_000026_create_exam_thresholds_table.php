<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->decimal('pass_score', 6, 2);
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
