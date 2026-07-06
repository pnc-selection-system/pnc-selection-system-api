<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('home_investigations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('investigator_id')->constrained('users')->onDelete('set null');
            $table->date('visit_date');
            $table->string('location', 255);
            $table->string('create_by');
            $table->enum('status', ['Assigned', 'In Progress', 'Submitted', 'Reviewed'])->default('Assigned');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_investigations');
    }
};
