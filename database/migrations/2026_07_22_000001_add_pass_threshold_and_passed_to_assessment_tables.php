<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add pass_threshold to assessment_forms
        if (! Schema::hasColumn('assessment_forms', 'pass_threshold')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->decimal('pass_threshold', 5, 2)->default(60)->after('schema');
            });
        }

        // Add passed column to assessment_responses
        if (! Schema::hasColumn('assessment_responses', 'passed')) {
            Schema::table('assessment_responses', function (Blueprint $table) {
                $table->boolean('passed')->nullable()->after('total_score');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('assessment_forms', 'pass_threshold')) {
            Schema::table('assessment_forms', function (Blueprint $table) {
                $table->dropColumn('pass_threshold');
            });
        }

        if (Schema::hasColumn('assessment_responses', 'passed')) {
            Schema::table('assessment_responses', function (Blueprint $table) {
                $table->dropColumn('passed');
            });
        }
    }
};
