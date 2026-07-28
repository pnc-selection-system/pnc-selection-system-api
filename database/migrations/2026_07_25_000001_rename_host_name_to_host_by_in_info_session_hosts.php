<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('info_session_hosts', function (Blueprint $table) {
            $table->renameColumn('host_name', 'host_by');
        });
    }

    public function down(): void
    {
        Schema::table('info_session_hosts', function (Blueprint $table) {
            $table->renameColumn('host_by', 'host_name');
        });
    }
};
