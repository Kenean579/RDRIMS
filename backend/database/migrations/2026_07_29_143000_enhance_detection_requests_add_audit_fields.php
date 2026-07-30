<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only add columns if they don't exist - handles both fresh installs and existing installs
        Schema::table('detection_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('detection_requests', 'completed_by')) {
                $table->unsignedBigInteger('completed_by')->nullable()->after('requested_at');
                $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('detection_requests', 'reviewed_at')) {
                $table->dateTime('reviewed_at')->nullable()->after('completed_at');
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
                $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('detection_requests', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('reviewed_by');
                $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('detection_requests', 'deleted_at')) {
                $table->softDeletes()->after('deleted_by');
            }
        });

        Schema::table('detection_results', function (Blueprint $table) {
            if (!Schema::hasColumn('detection_results', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('raw_response');
                $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('detection_results', 'deleted_at')) {
                $table->softDeletes()->after('deleted_by');
            }
        });

        // Ensure completed_at is explicitly nullable with no default
        try {
            DB::statement('ALTER TABLE detection_requests MODIFY COLUMN completed_at DATETIME NULL DEFAULT NULL');
        } catch (\Exception $e) {
            // Column may already be in correct state
        }
        
        // Add indexes if they don't exist (use raw SQL to avoid duplicates)
        try {
            if (!$this->indexExists('detection_requests', 'detection_requests_status_id_requested_by_index')) {
                DB::statement('ALTER TABLE detection_requests ADD INDEX detection_requests_status_id_requested_by_index(status_id, requested_by)');
            }
        } catch (\Exception $e) {}
        
        try {
            if (!$this->indexExists('detection_requests', 'detection_requests_completed_by_completed_at_index')) {
                DB::statement('ALTER TABLE detection_requests ADD INDEX detection_requests_completed_by_completed_at_index(completed_by, completed_at)');
            }
        } catch (\Exception $e) {}
        
        try {
            if (!$this->indexExists('detection_requests', 'detection_requests_reviewed_at_index')) {
                DB::statement('ALTER TABLE detection_requests ADD INDEX detection_requests_reviewed_at_index(reviewed_at)');
            }
        } catch (\Exception $e) {}
        
        try {
            if (!$this->indexExists('detection_requests', 'detection_requests_deleted_at_index')) {
                DB::statement('ALTER TABLE detection_requests ADD INDEX detection_requests_deleted_at_index(deleted_at)');
            }
        } catch (\Exception $e) {}
    }
    
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            "SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_NAME = ? AND INDEX_NAME = ?",
            [$table, $indexName]
        );
        return !empty($result);
    }

    public function down(): void
    {
        Schema::table('detection_results', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deleted_at']);
        });

        Schema::table('detection_requests', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['completed_by']);
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['deleted_by']);
            
            // Then drop indexes
            $table->dropIndex(['status_id', 'requested_by']);
            $table->dropIndex(['completed_by', 'completed_at']);
            $table->dropIndex(['reviewed_at']);
            $table->dropIndex(['deleted_at']);
            
            // Finally drop columns
            $table->dropColumn(['completed_by', 'reviewed_at', 'reviewed_by', 'deleted_by', 'deleted_at']);
        });
    }
};
