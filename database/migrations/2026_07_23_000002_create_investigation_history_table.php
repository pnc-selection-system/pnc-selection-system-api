<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('investigation_history')) {
            return;
        }

        Schema::create('investigation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')
                ->constrained('home_investigations')
                ->cascadeOnDelete();
            $table->string('action', 50); // Created, Updated, Submitted, Approved, Rejected
            $table->string('user_id', 100);
            $table->string('user_name', 255);
            $table->text('notes')->nullable();
            $table->timestamp('timestamp')->useCurrent();

            // Index for fast lookups
            $table->index('investigation_id', 'idx_history_investigation_id');
            $table->index('action', 'idx_history_action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_history');
    }
};
