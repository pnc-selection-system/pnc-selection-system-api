<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('import_files', 'ngo_id')) {
            Schema::table('import_files', function (Blueprint $table) {
                $table->foreignId('ngo_id')
                    ->nullable()
                    ->constrained('ngo_partners')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('import_files', function (Blueprint $table) {
            $table->dropForeign(['ngo_id']);
            $table->dropColumn('ngo_id');
        });
    }
};
