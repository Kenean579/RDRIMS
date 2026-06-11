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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('university_id')->nullable()->index()->after('user_id');
            $table->unsignedBigInteger('campus_id')->nullable()->index()->after('university_id');
            $table->unsignedBigInteger('faculty_id')->nullable()->index()->after('campus_id');
            $table->unsignedBigInteger('department_id')->nullable()->index()->after('faculty_id');
            $table->unsignedBigInteger('research_center_id')->nullable()->index()->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['university_id', 'campus_id', 'faculty_id', 'department_id', 'research_center_id']);
        });
    }
};
