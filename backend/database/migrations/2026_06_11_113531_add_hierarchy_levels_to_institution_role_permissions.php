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
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('campus_id')->nullable()->after('university_id')->index();
            $table->unsignedBigInteger('faculty_id')->nullable()->after('campus_id')->index();
            $table->unsignedBigInteger('department_id')->nullable()->after('faculty_id')->index();
            $table->unsignedBigInteger('research_center_id')->nullable()->after('department_id')->index();
        });

        Schema::table('institution_role_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('campus_id')->nullable()->after('university_id')->index();
            $table->unsignedBigInteger('faculty_id')->nullable()->after('campus_id')->index();
            $table->unsignedBigInteger('department_id')->nullable()->after('faculty_id')->index();
            $table->unsignedBigInteger('research_center_id')->nullable()->after('department_id')->index();
            
            // Allow null university if it's for another level
            $table->unsignedBigInteger('university_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['campus_id', 'faculty_id', 'department_id', 'research_center_id']);
        });

        Schema::table('institution_role_permissions', function (Blueprint $table) {
            $table->dropColumn(['campus_id', 'faculty_id', 'department_id', 'research_center_id']);
            $table->unsignedBigInteger('university_id')->nullable(false)->change();
        });
    }
};
