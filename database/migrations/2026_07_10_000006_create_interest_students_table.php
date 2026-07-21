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
            $table->foreignId('info_session_id')->constrained('info_sessions')->onDelete('cascade');
            $table->string('full_name', 150);
            $table->string('gender', 20);
            $table->string('phone', 30)->nullable();
            $table->string('school_grade', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('interested_students');
    }
};
