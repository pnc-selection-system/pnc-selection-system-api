<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
