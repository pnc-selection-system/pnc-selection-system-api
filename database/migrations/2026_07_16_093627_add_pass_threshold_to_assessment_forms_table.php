<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assessment_forms', function (Blueprint $table) {
            $table->decimal('pass_threshold', 5, 2)->default(60)->after('schema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_forms', function (Blueprint $table) {
            $table->dropColumn('pass_threshold');
        });
    }
};
