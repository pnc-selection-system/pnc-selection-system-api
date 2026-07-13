<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('info_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('session_date');

            $table->time('session_time');

            $table->string('location');

            $table->unsignedInteger('expected_attendance');

            $table->unsignedInteger('attendance_count')
                ->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('information_sessions');
    }
};
