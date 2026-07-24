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
            if (!Schema::hasColumn('exam_thresholds', 'must_pass_every_subject')) {
                $table->boolean('must_pass_every_subject')
                    ->default(false)
                    ->after('per_subject_min');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_thresholds', function (Blueprint $table) {
            if (Schema::hasColumn('exam_thresholds', 'must_pass_every_subject')) {
                $table->dropColumn('must_pass_every_subject');
            }
        });
    }
};
