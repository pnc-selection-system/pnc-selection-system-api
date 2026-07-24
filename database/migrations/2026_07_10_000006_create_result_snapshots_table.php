<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('result_snapshots')) {
            return;
        }

        Schema::create('result_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('selection_campaigns')
                ->onDelete('cascade');
            $table->unsignedBigInteger('candidate_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_data');
            $table->json('new_data');
            $table->integer('version');
            $table->text('reason');
            $table->foreignId('recalculated_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_snapshots');
    }
};
