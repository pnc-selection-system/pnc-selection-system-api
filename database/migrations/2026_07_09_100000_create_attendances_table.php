<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('info_session_id')->constrained('information_sessions')->onDelete('cascade');
            $table->integer('total_students')->default(0);
            $table->integer('male_students')->default(0);
            $table->integer('female_students')->default(0);
            $table->integer('teachers_attended')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};