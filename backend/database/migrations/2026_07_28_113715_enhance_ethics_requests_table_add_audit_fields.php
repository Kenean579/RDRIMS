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
        Schema::table('ethics_requests', function (Blueprint $table) {
            // Add audit fields
            $table->foreignId('created_by')->nullable()->after('version')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            
            // Add reviewer tracking (if not exists - check existing migration first)
            if (!Schema::hasColumn('ethics_requests', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('ethics_requests', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewer_id');
            }
            
            // Add soft deletes
            $table->softDeletes()->after('reviewed_at');
            
            // Add indexes for performance
            $table->index('created_by');
            $table->index('updated_by');
            if (!Schema::hasColumn('ethics_requests', 'reviewer_id')) {
                $table->index('reviewer_id');
            }
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ethics_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['created_by', 'updated_by']);
            // Don't drop reviewer_id and reviewed_at if they existed before
        });
    }
};
