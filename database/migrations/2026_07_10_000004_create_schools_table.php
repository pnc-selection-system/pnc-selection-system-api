<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('schools')) {
            return;
        }

        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            $table->foreignId('village_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 150);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('schools');
    }
};
