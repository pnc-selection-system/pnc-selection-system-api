<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table) {
            // Drop the existing exam_id foreign key and column
            $table->dropForeign(['exam_id']);
            $table->dropColumn('exam_id');

            // Add campaign_id referencing selection_campaigns
            $table->foreignId('campaign_id')
                ->after('id')
                ->constrained('selection_campaigns')
                ->onDelete('cascade');

            // Rename deduction_rule to deduction_rules for clarity
            $table->renameColumn('deduction_rule', 'deduction_rules');
        });
    }

    public function down(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->renameColumn('deduction_rules', 'deduction_rule');

            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');

            $table->foreignId('exam_id')
                ->after('id')
                ->constrained()
                ->onDelete('cascade');
        });
    }
};
