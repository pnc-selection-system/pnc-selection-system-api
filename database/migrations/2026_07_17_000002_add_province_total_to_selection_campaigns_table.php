<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('selection_campaigns', function (Blueprint $table) {
            $table->integer('province_total')->default(0)->after('condidate_total');
        });
    }

    public function down(): void
    {
        Schema::table('selection_campaigns', function (Blueprint $table) {
            $table->dropColumn('province_total');
        });
    }
};
