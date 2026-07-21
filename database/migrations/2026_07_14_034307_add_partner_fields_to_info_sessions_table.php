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
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->string('partner_type')->nullable()->comment('NGO or Officer');
            $table->string('ngo_name')->nullable()->comment('NGO name if partner_type is NGO');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('info_sessions', function (Blueprint $table) {
            $table->dropColumn(['partner_type', 'ngo_name']);
        });
    }
};
