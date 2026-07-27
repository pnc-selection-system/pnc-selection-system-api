<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration has been moved to 2026_07_10_000025_alter_exam_subjects_table.php
     * to fix ordering (needs to run AFTER 2026_07_10_000010_create_exam_subjects_table.php).
     * 
     * This stub is kept as a no-op to avoid breaking existing installations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('exam_subjects')) {
            return;
        }

        if (Schema::hasColumn('exam_subjects', 'exam_id')) {
            Schema::table('exam_subjects', function (Blueprint $table) {
                $table->dropForeign(['exam_id']);
                $table->dropColumn('exam_id');
            });
        }

        if (!Schema::hasColumn('exam_subjects', 'campaign_id')) {
            Schema::table('exam_subjects', function (Blueprint $table) {
                $table->foreignId('campaign_id')
                    ->after('id')
                    ->constrained('selection_campaigns')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasColumn('exam_subjects', 'deduction_rule') && !Schema::hasColumn('exam_subjects', 'deduction_rules')) {
            Schema::table('exam_subjects', function (Blueprint $table) {
                $table->renameColumn('deduction_rule', 'deduction_rules');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('exam_subjects')) {
            return;
        }

        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->renameColumn('deduction_rules', 'deduction_rule');

            if (Schema::hasColumn('exam_subjects', 'campaign_id')) {
                $table->dropForeign(['campaign_id']);
                $table->dropColumn('campaign_id');
            }

            if (!Schema::hasColumn('exam_subjects', 'exam_id')) {
                $table->foreignId('exam_id')
                    ->after('id')
                    ->constrained()
                    ->onDelete('cascade');
            }
        });
    }
};
