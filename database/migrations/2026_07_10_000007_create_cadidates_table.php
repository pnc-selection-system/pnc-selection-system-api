<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('candidates')) {
            return;
        }

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('selection_campaigns')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('ngo_id')->nullable()->constrained('ngo_partners')->onDelete('set null');

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->date('dob');
            $table->string('phone', 30);
            $table->string('email', 100)->nullable();
            $table->string('national', 50)->nullable();
            $table->string('photo', 255)->nullable();
            $table->text('address')->nullable();
            $table->integer('household_size')->nullable();
            $table->string('father_name', 100)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('guardian_name', 100)->nullable();
            $table->decimal('family_income', 10, 2)->nullable();
            $table->string('housing', 100)->nullable();
            $table->year('graduation_year')->nullable();
            $table->string('current_grade', 50)->nullable();
            $table->string('status', 50)->default('Pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidates');
    }
};
