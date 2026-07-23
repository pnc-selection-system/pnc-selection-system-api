<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_subjects')) {
            return;
        }

        Schema::table('exam_subjects', function (Blueprint $table) {
            if (Schema::hasColumn('exam_subjects', 'exam_id')) {
                $table->dropForeign(['exam_id']);
                $table->dropColumn('exam_id');
            }

            if (!Schema::hasColumn('exam_subjects', 'campaign_id')) {
                $table->foreignId('campaign_id')
                    ->after('id')
                    ->constrained('selection_campaigns')
                    ->onDelete('cascade');
            }

            if (Schema::hasColumn('exam_subjects', 'deduction_rule') && !Schema::hasColumn('exam_subjects', 'deduction_rules')) {
                $table->renameColumn('deduction_rule', 'deduction_rules');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('exam_subjects')) {
            return;
        }

        Schema::table('exam_subjects', function (Blueprint $table) {
            if (Schema::hasColumn('exam_subjects', 'deduction_rules') && !Schema::hasColumn('exam_subjects', 'deduction_rule')) {
                $table->renameColumn('deduction_rules', 'deduction_rule');
            }

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
