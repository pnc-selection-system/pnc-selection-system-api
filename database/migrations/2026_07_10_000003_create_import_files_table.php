<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('import_files')) {
            return;
        }

        Schema::create('import_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('selection_campaigns')->onDelete('cascade');
            $table->string('original_filename', 255);
            $table->string('stored_path', 500);
            $table->string('file_type', 10); // csv, xlsx
            $table->bigInteger('file_size');
            $table->json('detected_columns');      // [{index, name, sample_values}]
            $table->json('sample_rows');            // First few data rows for preview
            $table->integer('row_count')->default(0);
            $table->string('status', 20)->default('pending'); // pending, mapped, imported, error
            $table->text('error_message')->nullable();
            $table->foreignId('imported_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_files');
    }
};
