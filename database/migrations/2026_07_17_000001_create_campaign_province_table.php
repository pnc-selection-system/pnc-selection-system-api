<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_province', function (Blueprint $table) {
            $table->foreignId('selection_campaign_id')
                ->constrained('selection_campaigns')
                ->onDelete('cascade');
            $table->foreignId('province_id')
                ->constrained('provinces')
                ->onDelete('cascade');
            $table->primary(['selection_campaign_id', 'province_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_province');
    }
};
