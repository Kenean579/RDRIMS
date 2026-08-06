<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These fields were originally introduced by the earlier
        // 2026_06_12_031359 migration. Keep this later migration compatible
        // with databases where either field is unexpectedly missing.
        if (!Schema::hasColumn('proposals', 'originality_score')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->decimal('originality_score', 5, 2)
                    ->nullable()
                    ->after('status_id');
            });
        }

        if (!Schema::hasColumn('proposals', 'plagiarism_report_url')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->string('plagiarism_report_url')
                    ->nullable()
                    ->after('originality_score');
            });
        }
    }


    public function down(): void
    {
        // Do not drop these columns here: they belong to the earlier
        // 2026_06_12_031359 migration and may contain production data.
    }
};
