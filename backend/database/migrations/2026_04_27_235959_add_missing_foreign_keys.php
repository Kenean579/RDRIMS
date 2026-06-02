<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Users table deferred FKs
        if (Schema::hasTable('users') && Schema::hasTable('departments') && Schema::hasTable('files')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'department_id')) {
                    $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
                }
                if (Schema::hasColumn('users', 'profile_image_id')) {
                    $table->foreign('profile_image_id')->references('id')->on('files')->nullOnDelete();
                }
            });
        }

        // 2. Research Centers deferred FKs
        if (Schema::hasTable('research_centers') && Schema::hasTable('users') && Schema::hasTable('files')) {
            Schema::table('research_centers', function (Blueprint $table) {
                if (Schema::hasColumn('research_centers', 'director_id')) {
                    $table->foreign('director_id')->references('id')->on('users')->nullOnDelete();
                }
                if (Schema::hasColumn('research_centers', 'logo_file_id')) {
                    $table->foreign('logo_file_id')->references('id')->on('files')->nullOnDelete();
                }
            });
        }

        // 3. Calls table deferred FKs
        if (Schema::hasTable('calls') && Schema::hasTable('files')) {
            Schema::table('calls', function (Blueprint $table) {
                if (Schema::hasColumn('calls', 'guideline_file_id')) {
                    $table->foreign('guideline_file_id')->references('id')->on('files')->nullOnDelete();
                }
            });
        }

        // 4. Detection Requests deferred FKs
        if (Schema::hasTable('detection_requests') && Schema::hasTable('files')) {
            Schema::table('detection_requests', function (Blueprint $table) {
                if (Schema::hasColumn('detection_requests', 'file_id')) {
                    $table->foreign('file_id')->references('id')->on('files')->cascadeOnDelete();
                }
            });
        }

        // 5. Detection Results deferred FKs
        if (Schema::hasTable('detection_results') && Schema::hasTable('files')) {
            Schema::table('detection_results', function (Blueprint $table) {
                if (Schema::hasColumn('detection_results', 'report_file_id')) {
                    $table->foreign('report_file_id')->references('id')->on('files')->nullOnDelete();
                }
            });
        }

        // 6. Projects deferred FKs
        if (Schema::hasTable('projects') && Schema::hasTable('files')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'cover_image_id')) {
                    $table->foreign('cover_image_id')->references('id')->on('files')->nullOnDelete();
                }
            });
        }

        // 7. User Research Centers FK
        if (Schema::hasTable('user_research_centers') && Schema::hasTable('research_centers')) {
            Schema::table('user_research_centers', function (Blueprint $table) {
                $table->foreign('research_center_id')->references('id')->on('research_centers')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_research_centers')) {
            Schema::table('user_research_centers', function (Blueprint $table) {
                $table->dropForeignIfExists(['research_center_id']);
            });
        }
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeignIfExists(['department_id']);
                $table->dropForeignIfExists(['profile_image_id']);
            });
        }
        if (Schema::hasTable('research_centers')) {
            Schema::table('research_centers', function (Blueprint $table) {
                $table->dropForeignIfExists(['director_id']);
                $table->dropForeignIfExists(['logo_file_id']);
            });
        }
        if (Schema::hasTable('calls')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->dropForeignIfExists(['guideline_file_id']);
            });
        }
        if (Schema::hasTable('detection_requests')) {
            Schema::table('detection_requests', function (Blueprint $table) {
                $table->dropForeignIfExists(['file_id']);
            });
        }
        if (Schema::hasTable('detection_results')) {
            Schema::table('detection_results', function (Blueprint $table) {
                $table->dropForeignIfExists(['report_file_id']);
            });
        }
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropForeignIfExists(['cover_image_id']);
            });
        }
    }
};
