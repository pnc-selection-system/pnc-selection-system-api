<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_responses')) return;

        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessment_form_id')->constrained('assessment_forms')->onDelete('cascade');
            $table->json('answers');
            $table->decimal('total_score', 5, 2)->nullable();
            $table->boolean('passed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_responses');
    }
};
