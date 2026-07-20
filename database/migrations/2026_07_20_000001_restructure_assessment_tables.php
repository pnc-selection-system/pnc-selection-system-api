<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create assessment_questions table
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('assessment_forms')->onDelete('cascade');
            $table->string('key', 100);
            $table->string('label', 255);
            $table->string('type', 50)->default('text');
            $table->json('options')->nullable();
            $table->json('rules')->nullable();
            $table->decimal('weight', 5, 2)->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Drop old assessment_responses and recreate with new schema
        Schema::dropIfExists('assessment_responses');

        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('assessment_questions')->onDelete('cascade');
            $table->text('answer');
            $table->timestamps();

            $table->unique(['candidate_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_responses');
        Schema::dropIfExists('assessment_questions');

        // Restore old assessment_responses
        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('form_id')->constrained('assessment_forms')->onDelete('cascade');
            $table->json('answers');
            $table->decimal('total_score', 8, 2)->nullable();
            $table->timestamps();
        });
    }
};
