<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->foreign('logo_file_id')->references('id')->on('files')->nullOnDelete();
        });

        Schema::table('campuses', function (Blueprint $table) {
            $table->foreign('logo_file_id')->references('id')->on('files')->nullOnDelete();
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->foreign('logo_file_id')->references('id')->on('files')->nullOnDelete();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('logo_file_id')->references('id')->on('files')->nullOnDelete();
        });

        Schema::table('research_centers', function (Blueprint $table) {
            $table->foreign('logo_file_id')->references('id')->on('files')->nullOnDelete();
            $table->foreign('director_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('research_centers', function (Blueprint $table) {
            $table->dropForeign(['logo_file_id']);
            $table->dropForeign(['director_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['logo_file_id']);
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->dropForeign(['logo_file_id']);
        });

        Schema::table('campuses', function (Blueprint $table) {
            $table->dropForeign(['logo_file_id']);
        });

        Schema::table('universities', function (Blueprint $table) {
            $table->dropForeign(['logo_file_id']);
        });
    }
};