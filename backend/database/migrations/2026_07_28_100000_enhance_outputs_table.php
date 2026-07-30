<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            // Add audit fields
            $table->foreignId('created_by')->nullable()->after('research_center_id')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            
            // Add soft deletes
            $table->softDeletes();
            
            // Add indexes for performance
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('verified_by');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['verified_by']);
            
            $table->dropColumn([
                'created_by', 'updated_by', 'verified_by', 'verified_at', 'deleted_at'
            ]);
        });
    }
};
