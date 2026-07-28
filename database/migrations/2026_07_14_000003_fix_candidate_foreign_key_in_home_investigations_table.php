<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['candidate_id']);
            
            // Add the correct foreign key constraint pointing to candidates table
            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['candidate_id']);
            
            // Re-add the original constraint (which would fail, but that's the "down" behavior)
            $table->foreign('candidate_id')->references('id')->on('cadidates')->onDelete('cascade');
        });
    }
};