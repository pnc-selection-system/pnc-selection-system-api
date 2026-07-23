<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->string('location', 150)
                ->nullable()
                ->after('partner_name')
                ->comment('Location for Alumni sessions');

            $table->string('department', 150)
                ->nullable()
                ->after('location')
                ->comment('Department for Officer sessions');

            $table->string('generation', 100)
                ->nullable()
                ->after('department')
                ->comment('Generation for Alumni sessions');
        });
    }

    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->dropColumn(['location', 'department', 'generation']);
        });
    }
};
