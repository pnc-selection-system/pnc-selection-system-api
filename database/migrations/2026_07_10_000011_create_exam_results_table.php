<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('exam_results')) {
            return;
        }

        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('exam_subjects')->onDelete('cascade');
            $table->integer('raw_correct')->default(0);
            $table->integer('raw_wrong')->default(0);
            $table->decimal('raw_score', 6, 2);
            $table->decimal('deduction', 6, 2)->default(0);
            $table->decimal('final_score', 6, 2);
            $table->integer('rank')->nullable();
            $table->boolean('passed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_results');
    }
};
