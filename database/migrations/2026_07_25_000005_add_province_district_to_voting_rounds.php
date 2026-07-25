<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voting_rounds', function (Blueprint $table) {
            if (! Schema::hasColumn('voting_rounds', 'province_id')) {
                $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete()->after('campaign_id');
            }
            if (! Schema::hasColumn('voting_rounds', 'district_id')) {
                $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete()->after('province_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('voting_rounds', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['district_id']);
            $table->dropColumn(['province_id', 'district_id']);
        });
    }
};
