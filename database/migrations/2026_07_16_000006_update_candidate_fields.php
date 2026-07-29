<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Add Khmer name columns
            $table->string('first_name_kh', 100)->nullable()->after('last_name');
            $table->string('last_name_kh', 100)->nullable()->after('first_name_kh');
        });

        // Drop unused columns
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'national',
                'photo',
                'address',
                'household_size',
                'father_name',
                'mother_name',
                'guardian_name',
                'family_income',
                'housing',
                'graduation_year',
                'current_grade',
            ]);
        });
    }

    public function down()
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['first_name_kh', 'last_name_kh']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->after('phone');
            $table->string('national', 50)->nullable()->after('email');
            $table->string('photo', 255)->nullable()->after('national');
            $table->text('address')->nullable()->after('photo');
            $table->integer('household_size')->nullable()->after('address');
            $table->string('father_name', 100)->nullable()->after('household_size');
            $table->string('mother_name', 100)->nullable()->after('father_name');
            $table->string('guardian_name', 100)->nullable()->after('mother_name');
            $table->decimal('family_income', 10, 2)->nullable()->after('guardian_name');
            $table->string('housing', 100)->nullable()->after('family_income');
            $table->year('graduation_year')->nullable()->after('housing');
            $table->string('current_grade', 50)->nullable()->after('graduation_year');
        });
    }
};
