<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            // Add foreign key columns for province/district/commune (only if not already added)
            if (!Schema::hasColumn('info_sessions', 'province_id')) {
                $table->foreignId('province_id')
                    ->nullable()
                    ->after('campaign_id')
                    ->constrained('provinces')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('info_sessions', 'district_id')) {
                $table->foreignId('district_id')
                    ->nullable()
                    ->after('province_id')
                    ->constrained('districts')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('info_sessions', 'commune_id')) {
                $table->foreignId('commune_id')
                    ->nullable()
                    ->after('district_id')
                    ->constrained('communes')
                    ->cascadeOnDelete();
            }

            // Add host_by column (only if not already added)
            if (!Schema::hasColumn('info_sessions', 'host_by')) {
                $table->string('host_by', 150)
                    ->nullable()
                    ->after('attendance_count');
            }
        });

        // Rename columns (only if old column exists and new one doesn't)
        if (Schema::hasColumn('info_sessions', 'school_name') && !Schema::hasColumn('info_sessions', 'school')) {
            Schema::table('info_sessions', function (Blueprint $table) {
                $table->renameColumn('school_name', 'school');
            });
        }

        if (Schema::hasColumn('info_sessions', 'ngo_name') && !Schema::hasColumn('info_sessions', 'partner_name')) {
            Schema::table('info_sessions', function (Blueprint $table) {
                $table->renameColumn('ngo_name', 'partner_name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('info_sessions', 'province_id')) {
                $table->dropForeign(['province_id']);
            }
            if (Schema::hasColumn('info_sessions', 'district_id')) {
                $table->dropForeign(['district_id']);
            }
            if (Schema::hasColumn('info_sessions', 'commune_id')) {
                $table->dropForeign(['commune_id']);
            }

            $columnsToDrop = [];
            foreach (['province_id', 'district_id', 'commune_id', 'host_by'] as $col) {
                if (Schema::hasColumn('info_sessions', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            if (Schema::hasColumn('info_sessions', 'school') && !Schema::hasColumn('info_sessions', 'school_name')) {
                $table->renameColumn('school', 'school_name');
            }

            if (Schema::hasColumn('info_sessions', 'partner_name') && !Schema::hasColumn('info_sessions', 'ngo_name')) {
                $table->renameColumn('partner_name', 'ngo_name');
            }
        });
    }
};
