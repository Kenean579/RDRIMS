<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255);
            $table->text('value');
            $table->text('description')->nullable();

            // Hierarchical scoping — NULL means "applies to this level and below"
            $table->unsignedBigInteger('university_id')->nullable()->index();
            $table->unsignedBigInteger('campus_id')->nullable()->index();
            $table->unsignedBigInteger('faculty_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('research_center_id')->nullable()->index();

            $table->foreign('university_id')->references('id')->on('universities')->nullOnDelete();
            $table->foreign('campus_id')->references('id')->on('campuses')->nullOnDelete();
            $table->foreign('faculty_id')->references('id')->on('faculties')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('research_center_id')->references('id')->on('research_centers')->nullOnDelete();

            $table->unique(['key', 'university_id', 'campus_id', 'faculty_id', 'department_id', 'research_center_id'], 'inst_setting_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_settings');
    }
};
