<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->string('venue', 50)
                ->default('School')
                ->after('host_by')
                ->comment('Venue type: School, Alumni, NGO, Officer');
        });
    }

    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->dropColumn('venue');
        });
    }
};
