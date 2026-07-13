<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('information_sessions');
        Schema::enableForeignKeyConstraints();

        Schema::create('information_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('selection_campaigns')->cascadeOnDelete();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('commune_id')->nullable()->constrained('communes')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->enum('partner_type', ['ngo', 'pnc'])->nullable();
            $table->string('ngo_name', 150)->nullable();
            $table->string('ngo_contact', 150)->nullable();
            $table->string('pnc_department_id', 100)->nullable();
            $table->string('officer_id', 50)->nullable();
            $table->date('date');
            $table->time('time');
            $table->string('hosted_by', 150)->nullable();
            $table->integer('attendance_count')->default(0);
            $table->integer('total_participants')->default(0);
            $table->enum('status', ['upcoming', 'completed', 'cancelled'])->default('upcoming');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('information_sessions');
        Schema::enableForeignKeyConstraints();
    }
};
