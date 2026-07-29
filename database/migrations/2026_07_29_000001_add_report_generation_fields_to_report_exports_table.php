<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('report_exports', 'type')) {
                $table->string('type', 50)->default('final-selected-list')->after('report_name');
            }
            if (!Schema::hasColumn('report_exports', 'status')) {
                $table->string('status', 20)->default('processing')->after('export_type');
            }
            if (!Schema::hasColumn('report_exports', 'progress')) {
                $table->integer('progress')->nullable()->after('status');
            }
            if (!Schema::hasColumn('report_exports', 'file_path')) {
                $table->string('file_path', 500)->nullable()->after('progress');
            }
            if (!Schema::hasColumn('report_exports', 'completed_at')) {
                $table->dateTime('completed_at')->nullable()->after('created_at');
            }
            if (!Schema::hasColumn('report_exports', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_exports', function (Blueprint $table) {
            $columns = ['type', 'status', 'progress', 'file_path', 'completed_at', 'updated_at'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('report_exports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
