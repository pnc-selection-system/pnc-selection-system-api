<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            // Add campaign_id column
            if (!Schema::hasColumn('home_investigations', 'campaign_id')) {
                $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            }
            
            // Add people_met column
            if (!Schema::hasColumn('home_investigations', 'people_met')) {
                $table->text('people_met')->nullable();
            }
            
            // Add observations column
            if (!Schema::hasColumn('home_investigations', 'observations')) {
                $table->text('observations')->nullable();
            }
            
            // Add findings column
            if (!Schema::hasColumn('home_investigations', 'findings')) {
                $table->text('findings')->nullable();
            }
            
            // Add recommendation column
            if (!Schema::hasColumn('home_investigations', 'recommendation')) {
                $table->text('recommendation')->nullable();
            }
            
            // Add submitted_at column
            if (!Schema::hasColumn('home_investigations', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            
            // Add deleted_at column for soft deletes
            if (!Schema::hasColumn('home_investigations', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Remove create_by column if it exists (replaced by proper fields)
            if (Schema::hasColumn('home_investigations', 'create_by')) {
                $table->dropColumn('create_by');
            }
        });
    }

    public function down()
    {
        Schema::table('home_investigations', function (Blueprint $table) {
            // Drop the columns we added
            $table->dropForeign(['campaign_id']);
            $table->dropColumn([
                'campaign_id',
                'people_met',
                'observations',
                'findings',
                'recommendation',
                'submitted_at',
                'deleted_at'
            ]);
            
            // Add back create_by if it was removed
            $table->string('create_by')->nullable();
        });
    }
};