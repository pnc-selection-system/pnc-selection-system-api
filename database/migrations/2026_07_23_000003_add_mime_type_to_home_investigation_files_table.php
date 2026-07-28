<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_investigation_files', function (Blueprint $table) {
            if (!Schema::hasColumn('home_investigation_files', 'mime_type')) {
                $table->string('mime_type', 100)->nullable()->after('file_type');
            }


        });
    }

    public function down(): void
    {
        Schema::table('home_investigation_files', function (Blueprint $table) {
            $columns = ['mime_type', 'description'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('home_investigation_files', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
