<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
       Schema::create('candidate_status_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('candidate_id')
          ->constrained()
          ->cascadeOnDelete();
    $table->foreignId('changed_by')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();
    $table->date('changed_at');
     $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Withdrawn', 'Held', 'Selected'])->default('Pending')->nullable();
    $table->timestamps();
    });
    }

    public function down()
    {
        Schema::dropIfExists('candidate_status_histories');
    }
};
