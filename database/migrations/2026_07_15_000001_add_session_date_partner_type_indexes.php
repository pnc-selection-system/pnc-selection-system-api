<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->index('session_date');
            $table->index('partner_type');
        });
    }

    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->dropIndex(['session_date']);
            $table->dropIndex(['partner_type']);
        });
    }
};
