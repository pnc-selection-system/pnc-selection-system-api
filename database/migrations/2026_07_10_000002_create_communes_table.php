<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('communes')) {
            return;
        }

        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('name', 150);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('communes');
    }
};
