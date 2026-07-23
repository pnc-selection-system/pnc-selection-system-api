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
            // Drop the foreign key first
            $table->dropForeign(['province_id']);
            // Change column to nullable
            $table->foreignId('province_id')->nullable()->change();
            // Re-add the foreign key
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_files', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['province_id']);
            // Make it not nullable again
            $table->foreignId('province_id')->change();
            // Re-add the foreign key
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
        });
    }
};
