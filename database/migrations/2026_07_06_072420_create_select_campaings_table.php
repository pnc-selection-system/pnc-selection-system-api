<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('selection_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->year('year');
            $table->integer('condidate_total');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['Draft', 'Active', 'Closed'])->default('Draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('selection_campaigns');
    }
};
