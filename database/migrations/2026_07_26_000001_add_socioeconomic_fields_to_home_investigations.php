<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            if (!Schema::hasColumn('home_investigations', 'family_size')) {
                $table->integer('family_size')->nullable()->after('current_address');
            }
            if (!Schema::hasColumn('home_investigations', 'monthly_income')) {
                $table->decimal('monthly_income', 12, 2)->nullable()->after('family_size');
            }
            if (!Schema::hasColumn('home_investigations', 'occupation')) {
                $table->string('occupation', 255)->nullable()->after('monthly_income');
            }
            if (!Schema::hasColumn('home_investigations', 'housing_type')) {
                $table->string('housing_type', 100)->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('home_investigations', 'disability')) {
                $table->string('disability', 100)->nullable()->after('housing_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            $columns = ['family_size', 'monthly_income', 'occupation', 'housing_type', 'disability'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('home_investigations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
