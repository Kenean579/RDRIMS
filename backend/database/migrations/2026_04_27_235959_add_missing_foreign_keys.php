<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Users table constraints
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('profile_image_id')->references('id')->on('files')->nullOnDelete();
        });

        // 2. Research Centers table constraints
        Schema::table('research_centers', function (Blueprint $table) {
            $table->foreign('director_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('logo_file_id')->references('id')->on('files')->nullOnDelete();
        });

        // 3. Calls table constraints
        Schema::table('calls', function (Blueprint $table) {
            $table->foreign('guideline_file_id')->references('id')->on('files')->nullOnDelete();
        });

        // 4. Proposals table constraints
        Schema::table('proposals', function (Blueprint $table) {
            $table->foreign('file_id')->references('id')->on('files')->nullOnDelete();
            $table->foreign('ethics_file_id')->references('id')->on('files')->nullOnDelete();
        });

        // 5. Detection Requests table constraints
        Schema::table('detection_requests', function (Blueprint $table) {
            $table->foreign('file_id')->references('id')->on('files')->cascadeOnDelete();
        });

        // 6. Detection Results table constraints
        Schema::table('detection_results', function (Blueprint $table) {
            $table->foreign('report_file_id')->references('id')->on('files')->nullOnDelete();
        });

        // 7. Projects table constraints
        Schema::table('projects', function (Blueprint $table) {
            // Already has cover_image_id unsignedBigInteger or similar? 
            // Let's be safe and just define the foreign key.
            $table->foreign('cover_image_id')->references('id')->on('files')->nullOnDelete();
        });

        Schema::table('user_research_centers', function (Blueprint $table) {
            $table->foreign('research_center_id')->references('id')->on('research_centers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_research_centers', function (Blueprint $table) {
            $table->dropForeign(['research_center_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['profile_image_id']);
        });
        Schema::table('research_centers', function (Blueprint $table) {
            $table->dropForeign(['director_id']);
            $table->dropForeign(['logo_file_id']);
        });
        Schema::table('calls', function (Blueprint $table) {
            $table->dropForeign(['guideline_file_id']);
        });
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
            $table->dropForeign(['ethics_file_id']);
        });
        Schema::table('detection_requests', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
        });
        Schema::table('detection_results', function (Blueprint $table) {
            $table->dropForeign(['report_file_id']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['cover_image_id']);
        });
    }
};
