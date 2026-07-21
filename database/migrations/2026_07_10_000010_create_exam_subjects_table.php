<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('selection_campaigns')
                ->onDelete('cascade');
            $table->string('name', 100);
            $table->decimal('max_score', 6, 2);
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_subjects');
    }
};
