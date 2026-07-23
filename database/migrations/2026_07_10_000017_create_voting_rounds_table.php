<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('voting_rounds')) {
            return;
        }

        Schema::create('voting_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('selection_campaigns')->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('voting_method', ['Majority', 'Weighted']);
            $table->enum('status', ['Open', 'Closed'])->default('Open');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('voting_rounds');
    }
};
