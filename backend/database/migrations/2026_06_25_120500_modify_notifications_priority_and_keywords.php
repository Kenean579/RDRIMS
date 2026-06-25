<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure priority column has proper enum definition and default
        DB::statement("ALTER TABLE notifications MODIFY COLUMN priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium'");
        // Make keywords column nullable with default empty string
        DB::statement("ALTER TABLE notifications MODIFY COLUMN keywords VARCHAR(255) NULL DEFAULT ''");
    }

    public function down(): void
    {
        // Revert modifications (allow nulls again for keywords)
        DB::statement("ALTER TABLE notifications MODIFY COLUMN keywords VARCHAR(255) NULL");
        // Keep priority as is (cannot revert without losing data)
    }
};
