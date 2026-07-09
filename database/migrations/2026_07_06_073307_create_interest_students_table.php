<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('interested_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('info_session_id')->constrained('information_sessions')->onDelete('cascade');
            $table->string('full_name', 150);
            $table->string('gender', 20);
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('current_grade', 50)->nullable();
            $table->string('school_name', 150)->nullable();
            $table->string('preferred_major', 150)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['interested', 'contacted', 'application_started', 'application_submitted', 'converted'])->default('interested');
            $table->foreignId('converted_to_candidate_id')->nullable()->constrained('cadidates')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('interested_students');
    }
};
