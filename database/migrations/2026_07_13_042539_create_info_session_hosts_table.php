<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_session_hosts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('info_session_id')
                ->constrained('info_sessions')
                ->cascadeOnDelete();

            $table->string('host_name', 150);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_session_hosts');
    }
};