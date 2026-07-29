<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_questions')) return;

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_form_id')->constrained('assessment_forms')->onDelete('cascade');
            $table->string('key');
            $table->string('label');
            $table->string('type');
            $table->integer('order')->default(0);
            $table->integer('weight')->default(1);
            $table->json('options')->nullable();
            $table->json('point_map')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
