<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('entity', 100);
            $table->bigInteger('entity_id');
            $table->string('action', 50);
            $table->json('diff')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('created_at');
            // No updated_at for logs
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
