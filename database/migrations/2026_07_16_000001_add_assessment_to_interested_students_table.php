<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interested_students', function (Blueprint $table) {
            $table->json('assessment_answers')->nullable()->after('school_grade');
            $table->decimal('total_score', 8, 2)->nullable()->after('assessment_answers');
        });
    }

    public function down(): void
    {
        Schema::table('interested_students', function (Blueprint $table) {
            $table->dropColumn(['assessment_answers', 'total_score']);
        });
    }
};
