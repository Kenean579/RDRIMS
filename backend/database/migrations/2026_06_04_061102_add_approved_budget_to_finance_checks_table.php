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
        Schema::table('finance_checks', function (Blueprint $table) {
            $table->decimal('approved_budget', 15, 2)->nullable()->after('comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_checks', function (Blueprint $table) {
            $table->dropColumn('approved_budget');
        });
    }
};
