<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ethics_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('ethics_requests', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('ethics_requests', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
        });

        DB::table('ethics_approval_statuses')->insertOrIgnore([
            ['name' => 'under_review', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'needs_revision', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    public function down(): void
    {
        Schema::table('ethics_requests', function (Blueprint $table) {
            $table->dropForeign(['reviewer_id']);
            $table->dropColumn(['reviewer_id', 'reviewed_at']);
        });

        DB::table('ethics_approval_statuses')->whereIn('name', ['under_review', 'needs_revision'])->delete();
    }
};
