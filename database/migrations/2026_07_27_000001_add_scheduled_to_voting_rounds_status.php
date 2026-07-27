<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires altering the ENUM column to change its allowed values
        DB::statement("ALTER TABLE `voting_rounds` MODIFY `status` ENUM('Scheduled', 'Open', 'Closed') NOT NULL DEFAULT 'Open'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `voting_rounds` MODIFY `status` ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open'");
    }
};
