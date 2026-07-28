<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('candidate_status_histories')) {
            return;
        }

        Schema::create('candidate_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->string('status', 50);
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('changed_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidate_status_histories');
    }
};
