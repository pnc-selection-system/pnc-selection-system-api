<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First set any empty string schools to null to avoid truncation issues
        DB::statement("UPDATE info_sessions SET school = NULL WHERE school = ''");

        Schema::table('info_sessions', function (Blueprint $table) {
            $table->string('school', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->string('school', 150)->nullable(false)->change();
        });
    }
};
