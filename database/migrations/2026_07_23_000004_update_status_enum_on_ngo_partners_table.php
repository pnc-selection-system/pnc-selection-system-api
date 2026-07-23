<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize existing data to lowercase
        DB::table('ngo_partners')->where('status', 'Active')->update(['status' => 'active']);
        DB::table('ngo_partners')->where('status', 'Inactive')->update(['status' => 'inactive']);
        DB::table('ngo_partners')->whereNull('status')->orWhere('status', '')->update(['status' => 'active']);

        // Change column to enum
        DB::statement("ALTER TABLE ngo_partners MODIFY COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ngo_partners MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Active'");
    }
};
