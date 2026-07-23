<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration has been moved to 2026_07_10_000028_add_status_and_version_to_results.php
     * to fix ordering (needs to run AFTER 2026_07_10_000011_create_exam_results_table.php and
     * 2026_07_10_000027_alter_exam_results_add_campaign_and_overall.php).
     * 
     * This stub is kept as a no-op to avoid breaking existing installations.
     */
    public function up(): void
    {
        if (Schema::hasTable('exam_results') && !Schema::hasColumn('exam_results', 'status')) {
            Schema::table('exam_results', function (Blueprint $table) {
                $table->string('status', 20)
                    ->default('draft')
                    ->after('passed')
                    ->comment('draft|published|locked');
                $table->integer('version')
                    ->default(1)
                    ->after('status');
            });
        }

        if (Schema::hasTable('exam_overall_results') && !Schema::hasColumn('exam_overall_results', 'status')) {
            Schema::table('exam_overall_results', function (Blueprint $table) {
                $table->string('status', 20)
                    ->default('draft')
                    ->after('passed')
                    ->comment('draft|published|locked');
                $table->integer('version')
                    ->default(1)
                    ->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_results')) {
            Schema::table('exam_results', function (Blueprint $table) {
                if (Schema::hasColumn('exam_results', 'status')) {
                    $table->dropColumn(['status', 'version']);
                }
            });
        }

        if (Schema::hasTable('exam_overall_results')) {
            Schema::table('exam_overall_results', function (Blueprint $table) {
                if (Schema::hasColumn('exam_overall_results', 'status')) {
                    $table->dropColumn(['status', 'version']);
                }
            });
        }
    }
};
