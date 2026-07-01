<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make checked_at nullable in finance_checks table
        Schema::table('finance_checks', function (Blueprint $table) {
            $table->dateTime('checked_at')->nullable()->change();
        });

        // 2. Insert 'ethics_pending' status into proposal_statuses table
        DB::table('proposal_statuses')->insertOrIgnore([
            ['name' => 'ethics_pending', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    public function down(): void
    {
        // 1. Revert checked_at to non-nullable (make sure not to break if nulls exist, but since it's down, we try)
        Schema::table('finance_checks', function (Blueprint $table) {
            $table->dateTime('checked_at')->nullable(false)->change();
        });

        // 2. Delete 'ethics_pending'
        DB::table('proposal_statuses')->where('name', 'ethics_pending')->delete();
    }
};
