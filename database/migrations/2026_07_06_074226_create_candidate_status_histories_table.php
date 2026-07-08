<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
<<<<<<< HEAD
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
=======
        Schema::create('candidate_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->string('status', 50);
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('changed_at');
            $table->timestamps();
        });
>>>>>>> 87cb885b5de82731a26b1818c20af4145087198b
    }

    public function down()
    {
        Schema::dropIfExists('candidate_status_histories');
    }
};
