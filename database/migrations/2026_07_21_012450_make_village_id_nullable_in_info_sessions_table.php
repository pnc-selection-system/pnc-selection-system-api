<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->dropForeign(['village_id']);
        });

        Schema::table('info_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('village_id')->nullable()->change();
        });

        Schema::table('info_sessions', function (Blueprint $table) {
            $table->foreign('village_id')->references('id')->on('villages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->dropForeign(['village_id']);
        });

        Schema::table('info_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('village_id')->nullable(false)->change();
        });

        Schema::table('info_sessions', function (Blueprint $table) {
            $table->foreign('village_id')->references('id')->on('villages')->cascadeOnDelete();
        });
    }
};
