<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing 'Draft' records to 'Active'
        DB::statement("UPDATE selection_campaigns SET status = 'Active' WHERE status = 'Draft'");

        // Alter the ENUM column to only allow 'Active' and 'Closed'
        DB::statement("ALTER TABLE selection_campaigns MODIFY COLUMN status ENUM('Active', 'Closed') NOT NULL DEFAULT 'Active'");
    }

    public function down(): void
    {
        // Restore the original ENUM values
        DB::statement("ALTER TABLE selection_campaigns MODIFY COLUMN status ENUM('Draft', 'Active', 'Closed') NOT NULL DEFAULT 'Draft'");
    }
};
