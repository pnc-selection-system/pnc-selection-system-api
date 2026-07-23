<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->time('session_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->time('session_time')->nullable(false)->change();
        });
    }
};
