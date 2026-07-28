<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            // Denormalized fields for fast display (flow doc section 2.1)
            if (!Schema::hasColumn('home_investigations', 'candidate_name')) {
                $table->string('candidate_name', 255)->nullable()->after('candidate_id');
            }
            if (!Schema::hasColumn('home_investigations', 'campaign')) {
                $table->string('campaign', 255)->nullable()->after('candidate_name');
            }
            if (!Schema::hasColumn('home_investigations', 'gender')) {
                $table->string('gender', 20)->nullable()->after('campaign');
            }
            if (!Schema::hasColumn('home_investigations', 'phone_number')) {
                $table->string('phone_number', 50)->nullable()->after('gender');
            }
            if (!Schema::hasColumn('home_investigations', 'current_address')) {
                $table->text('current_address')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('home_investigations', 'assigned_investigator')) {
                $table->string('assigned_investigator', 255)->nullable()->after('investigator_id');
            }

            // Add unique constraint on candidate_id
            $table->unique('candidate_id', 'uq_candidate_id');

            // Make visit_date nullable (flow doc allows null)
            if (Schema::hasColumn('home_investigations', 'visit_date')) {
                DB::statement('ALTER TABLE home_investigations MODIFY COLUMN visit_date DATE NULL');
            }

            // Change location to TEXT and nullable (flow doc specifies TEXT)
            if (Schema::hasColumn('home_investigations', 'location')) {
                DB::statement('ALTER TABLE home_investigations MODIFY COLUMN location TEXT NULL');
            }

            // Additional form fields
            if (!Schema::hasColumn('home_investigations', 'gps_coordinates')) {
                $table->string('gps_coordinates', 100)->nullable()->after('location');
            }
            if (!Schema::hasColumn('home_investigations', 'reason')) {
                $table->text('reason')->nullable()->after('recommendation');
            }

            // 5-status system timestamps and fields
            if (!Schema::hasColumn('home_investigations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('home_investigations', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('home_investigations', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('home_investigations', 'notes')) {
                $table->text('notes')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('home_investigations', 'summary')) {
                $table->text('summary')->nullable()->after('notes');
            }

            // Indexes for performance (matching flow doc requirements)
            if (!Schema::hasIndex('home_investigations', 'idx_candidate_id')) {
                $table->index('candidate_id', 'idx_candidate_id');
            }
            if (!Schema::hasIndex('home_investigations', 'idx_current_status')) {
                $table->index('status', 'idx_current_status');
            }
            if (!Schema::hasIndex('home_investigations', 'idx_campaign')) {
                $table->index('campaign', 'idx_campaign');
            }
            if (!Schema::hasIndex('home_investigations', 'idx_assigned_investigator')) {
                $table->index('assigned_investigator', 'idx_assigned_investigator');
            }
            if (!Schema::hasIndex('home_investigations', 'idx_investigator_status')) {
                $table->index(['assigned_investigator', 'status'], 'idx_investigator_status');
            }
        });

        // Update the ENUM to support both 3-status and 5-status values
        DB::statement("ALTER TABLE home_investigations CHANGE COLUMN status status ENUM(
            'Pending', 'Assigned', 'In Progress', 'Submitted', 'Approved', 'Rejected', 'Reviewed'
        ) NOT NULL DEFAULT 'Assigned'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE home_investigations CHANGE COLUMN status status ENUM(
            'Assigned', 'In Progress', 'Submitted', 'Reviewed'
        ) NOT NULL DEFAULT 'Assigned'");

        Schema::table('home_investigations', function (Blueprint $table) {
            $columns = [
                'candidate_name', 'campaign', 'gender', 'phone_number',
                'current_address', 'assigned_investigator', 'gps_coordinates',
                'reason', 'approved_at', 'rejected_at', 'rejection_reason',
                'notes', 'summary',
            ];

            $indexes = [
                'uq_candidate_id',
                'idx_candidate_id', 'idx_current_status', 'idx_campaign', 'idx_assigned_investigator',
                'idx_investigator_status',
            ];

            foreach ($indexes as $index) {
                if (Schema::hasIndex('home_investigations', $index)) {
                    $table->dropIndex($index);
                }
            }

            // Restore visit_date and location to NOT NULL
            if (Schema::hasColumn('home_investigations', 'visit_date')) {
                DB::statement('ALTER TABLE home_investigations MODIFY COLUMN visit_date DATE NOT NULL');
            }
            if (Schema::hasColumn('home_investigations', 'location')) {
                DB::statement('ALTER TABLE home_investigations MODIFY COLUMN location VARCHAR(255) NOT NULL');
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn('home_investigations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
