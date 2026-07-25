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
        Schema::table('import_files', function (Blueprint $table) {
            $table->foreignId('ngo_id')
                ->after('province_id')
                ->nullable()
                ->constrained('ngo_partners')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_files', function (Blueprint $table) {
            $table->dropForeign(['ngo_id']);
            $table->dropColumn('ngo_id');
        });
    }
};
