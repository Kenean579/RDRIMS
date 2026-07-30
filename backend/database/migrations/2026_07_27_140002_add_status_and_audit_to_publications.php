<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            // Add status and type
            $table->unsignedInteger('status_id')->default(1)->after('id');
            $table->foreign('status_id')->references('id')->on('publication_statuses')->restrictOnDelete();
            
            $table->unsignedInteger('type_id')->nullable()->after('status_id');
            $table->foreign('type_id')->references('id')->on('publication_types')->nullOnDelete();
            
            // Add missing publication fields
            $table->string('isbn', 50)->nullable()->after('doi');
            $table->string('issn', 50)->nullable()->after('isbn');
            $table->string('volume', 50)->nullable()->after('journal');
            $table->string('issue', 50)->nullable()->after('volume');
            $table->string('pages', 50)->nullable()->after('issue');
            $table->string('publisher', 255)->nullable()->after('pages');
            $table->string('conference_name', 255)->nullable()->after('publisher');
            
            // Add audit fields
            $table->foreignId('created_by')->nullable()->after('research_center_id')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            
            // Add soft deletes
            $table->softDeletes();
            
            // Add indexes
            $table->index('status_id');
            $table->index('type_id');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('verified_by');
            $table->index('deleted_at');
            $table->index('isbn');
            $table->index('issn');
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropForeign(['type_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['verified_by']);
            
            $table->dropColumn([
                'status_id', 'type_id', 'isbn', 'issn', 'volume', 'issue', 
                'pages', 'publisher', 'conference_name', 
                'created_by', 'updated_by', 'verified_by', 'verified_at', 'deleted_at'
            ]);
        });
    }
};
