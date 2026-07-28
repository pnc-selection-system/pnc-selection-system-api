<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('votes')) {
            return;
        }

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_round_id')->constrained('voting_rounds')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained('users')->onDelete('cascade');
            $table->enum('decision', ['Approve', 'Reject', 'Abstain']);
            $table->text('comment')->nullable();
            $table->dateTime('voted_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('votes');
    }
};
