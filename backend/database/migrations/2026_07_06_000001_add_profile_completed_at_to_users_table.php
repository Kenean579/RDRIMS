<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `profile_completed_at` to the users table.
 *
 * This column is NULL for admin-provisioned users who have not yet
 * completed their profile after activating their account.
 * It is also NULL for self-registered users until they voluntarily
 * complete their profile.
 *
 * The frontend uses this value to soft-redirect users to the profile
 * completion page. No backend hard-gate is applied by default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('profile_completed_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_completed_at');
        });
    }
};
