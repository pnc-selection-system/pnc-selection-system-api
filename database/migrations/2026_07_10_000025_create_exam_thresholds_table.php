<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Superceded by 2026_07_10_000026_create_exam_thresholds_table.php.
     * Kept as a true no-op to avoid breaking existing installations.
     */
    public function up(): void
    {
        // No-op
    }

    public function down(): void
    {
        // No-op
    }
};
