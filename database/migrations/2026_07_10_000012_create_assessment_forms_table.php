<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('assessment_forms')) {
            return;
        }

        Schema::create('assessment_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('selection_campaigns')->onDelete('cascade');
            $table->string('name', 100);
            $table->json('schema');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessment_forms');
    }
};
