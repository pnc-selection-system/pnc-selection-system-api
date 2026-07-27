<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_files', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->unsignedBigInteger('province_id')->nullable()->change();
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Set any null province_ids to the first province before reverting
        $firstProvinceId = DB::table('provinces')->value('id');
        if ($firstProvinceId) {
            DB::table('import_files')->whereNull('province_id')->update(['province_id' => $firstProvinceId]);
        }

        Schema::table('import_files', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->unsignedBigInteger('province_id')->nullable(false)->change();
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
        });
    }
};
