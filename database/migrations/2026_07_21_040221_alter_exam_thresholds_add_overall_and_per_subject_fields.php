<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_thresholds', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_thresholds', 'overall_pass_mark')) {
                $table->decimal('overall_pass_mark', 6, 2)->nullable()->after('subject_id');
            }
            if (!Schema::hasColumn('exam_thresholds', 'per_subject_min')) {
                $table->decimal('per_subject_min', 6, 2)->nullable()->after('overall_pass_mark');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_thresholds', function (Blueprint $table) {
            $table->dropColumn(['overall_pass_mark', 'per_subject_min']);
        });
    }
};
