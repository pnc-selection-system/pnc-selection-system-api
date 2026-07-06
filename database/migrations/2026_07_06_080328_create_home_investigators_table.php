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
        Schema::create('home_investigators', function (Blueprint $table) {
            $table->id();

            $table->foreignId('home_investigation_id')
                ->constrained('home_investigations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('investigator_name');
            $table->text('people_met')->nullable();
            $table->text('findings')->nullable();

            $table->enum('recommendation', [
                'approve',
                'reject',
                'review'
            ]);

            $table->dateTime('submitted')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_investigators');
    }
};
