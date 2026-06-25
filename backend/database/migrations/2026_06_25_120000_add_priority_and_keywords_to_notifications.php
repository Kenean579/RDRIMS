<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Migration deprecated: priority column already added in earlier migration.
    }

    public function down(): void
    {
        // No operation – priority column handled elsewhere.
    }
};
