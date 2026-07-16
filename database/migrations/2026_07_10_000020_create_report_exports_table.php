<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('report_name', 150);
            $table->enum('export_type', ['PDF', 'Excel']);
            $table->foreignId('campaign_id')->constrained('selection_campaigns')->onDelete('cascade');
            $table->dateTime('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_exports');
    }
};
