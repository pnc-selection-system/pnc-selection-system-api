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
            $table->index('campaign_id');
            $table->index('village_id');
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->index('commune_id');
        });

        Schema::table('communes', function (Blueprint $table) {
            $table->index('district_id');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->index('province_id');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->index('campaign_id');
            $table->index('publish_status');
        });

        Schema::table('assessment_forms', function (Blueprint $table) {
            $table->index('campaign_id');
        });
    }

    public function down(): void
    {
        Schema::table('info_sessions', fn (Blueprint $t) => $t->dropIndex(['campaign_id', 'village_id']));
        Schema::table('villages', fn (Blueprint $t) => $t->dropIndex(['commune_id']));
        Schema::table('communes', fn (Blueprint $t) => $t->dropIndex(['district_id']));
        Schema::table('districts', fn (Blueprint $t) => $t->dropIndex(['province_id']));
        Schema::table('exams', fn (Blueprint $t) => $t->dropIndex(['campaign_id', 'publish_status']));
        Schema::table('assessment_forms', fn (Blueprint $t) => $t->dropIndex(['campaign_id']));
    }
};
