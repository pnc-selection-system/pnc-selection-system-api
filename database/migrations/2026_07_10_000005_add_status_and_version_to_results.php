<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->string('status', 20)
                ->default('draft')
                ->after('passed')
                ->comment('draft|published|locked');
            $table->integer('version')
                ->default(1)
                ->after('status');
        });

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

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn(['status', 'version']);
        });

        Schema::table('exam_overall_results', function (Blueprint $table) {
            $table->dropColumn(['status', 'version']);
        });
    }
};
