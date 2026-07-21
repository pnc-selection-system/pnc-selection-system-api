<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            // Add foreign key columns for province/district/commune
            $table->foreignId('province_id')
                ->nullable()
                ->after('campaign_id')
                ->constrained('provinces')
                ->cascadeOnDelete();

            $table->foreignId('district_id')
                ->nullable()
                ->after('province_id')
                ->constrained('districts')
                ->cascadeOnDelete();

            $table->foreignId('commune_id')
                ->nullable()
                ->after('district_id')
                ->constrained('communes')
                ->cascadeOnDelete();

            // Add host_by column
            $table->string('host_by', 150)
                ->nullable()
                ->after('attendance_count');
        });

        // Rename columns (renameColumn needs to be outside the callback)
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->renameColumn('school_name', 'school');
        });

        Schema::table('info_sessions', function (Blueprint $table) {
            $table->renameColumn('ngo_name', 'partner_name');
        });
    }

    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['commune_id']);
            $table->dropColumn(['province_id', 'district_id', 'commune_id', 'host_by']);
            $table->renameColumn('school', 'school_name');
            $table->renameColumn('partner_name', 'ngo_name');
        });
    }
};
