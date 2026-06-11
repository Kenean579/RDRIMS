<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('university_id')->references('id')->on('universities')->nullOnDelete();
            $table->foreign('research_center_id')->references('id')->on('research_centers')->nullOnDelete();
            $table->foreign('center_role_id')->references('id')->on('center_roles')->nullOnDelete();
            $table->foreign('profile_image_id')->references('id')->on('files')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['university_id']);
            $table->dropForeign(['research_center_id']);
            $table->dropForeign(['center_role_id']);
            $table->dropForeign(['profile_image_id']);
        });
    }
};