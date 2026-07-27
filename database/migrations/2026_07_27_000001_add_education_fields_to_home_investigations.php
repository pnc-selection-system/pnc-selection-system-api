<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            $table->string('education_level')->nullable()->after('disability');
            $table->string('school_name')->nullable()->after('education_level');
            $table->string('major')->nullable()->after('school_name');
            $table->string('graduation_year', 4)->nullable()->after('major');
            $table->decimal('gpa', 4, 2)->nullable()->after('graduation_year');
        });
    }

    public function down(): void
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            $table->dropColumn(['education_level', 'school_name', 'major', 'graduation_year', 'gpa']);
        });
    }
};
