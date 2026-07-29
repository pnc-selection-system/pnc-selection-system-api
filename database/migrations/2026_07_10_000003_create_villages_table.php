<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('villages')) {
            return;
        }

        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_id')->constrained('communes')->onDelete('cascade');
            $table->string('name', 150);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('villages');
    }
};
