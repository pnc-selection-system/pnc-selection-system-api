<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the foreign key and column
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });

        // Add school_name and update default status
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('school_name', 200)->nullable()->after('province_id');
        });

        // Change default status from 'Pending' to 'Register'
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('status', 50)->default('Register')->change();
        });
    }

    public function down()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('school_name');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->string('status', 50)->default('Register')->change();
        });
    }
};
