<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('assessment_responses')) {
            return;
        }

        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('form_id')->constrained('assessment_forms')->onDelete('cascade');
            $table->json('answers');
            $table->decimal('total_score', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessment_responses');
    }
};
