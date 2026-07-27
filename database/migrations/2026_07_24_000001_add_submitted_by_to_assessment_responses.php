<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_responses', function (Blueprint $table) {
            if (! Schema::hasColumn('assessment_responses', 'submitted_by')) {
                $table->foreignId('submitted_by')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessment_responses', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_responses', 'submitted_by')) {
                $table->dropConstrainedForeignId('submitted_by');
            }
        });
    }
};
