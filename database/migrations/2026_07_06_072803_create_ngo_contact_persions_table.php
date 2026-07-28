<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ngo_contact_persons')) {
            return;
        }

        Schema::create('ngo_contact_persons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ngo_partner_id')->constrained('ngo_partners')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('full_name', 100);
            $table->string('email', 100);
            $table->string('phone', 30);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngo_contact_persons');
    }
};
