<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_file_id')->constrained('import_files')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('selection_campaigns')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('exam_subjects')->onDelete('cascade');
            $table->foreignId('imported_by')->constrained('users')->onDelete('cascade');
            $table->integer('total_rows')->default(0);
            $table->integer('imported_rows')->default(0);
            $table->integer('errored_rows')->default(0);
            $table->json('column_mapping')->nullable();
            $table->string('status', 20)->default('pending'); // pending, imported, error
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_exam_results');
    }
};
