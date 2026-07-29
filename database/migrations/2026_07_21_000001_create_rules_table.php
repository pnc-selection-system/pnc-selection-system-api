<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_subject_id')
                ->constrained('exam_subjects')
                ->onDelete('cascade');
            $table->string('name', 100);
            $table->text('desc')->nullable();
            $table->enum('sign', ['+', '-', '*', '%']);
            $table->decimal('value', 5, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rules');
    }
};
