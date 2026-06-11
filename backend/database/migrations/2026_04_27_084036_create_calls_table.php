<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->date('deadline')->index();
            $table->text('thematic_areas');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('status_id')->index();
            $table->foreign('status_id')->references('id')->on('call_statuses')->restrictOnDelete();

            $table->foreignId('academic_year_id')
                                ->nullable()->constrained('academic_years')
                                ->nullOnDelete();

            $table->unsignedBigInteger('guideline_file_id')->nullable()->index();

            $table->unsignedBigInteger('university_id')->nullable()->index();
            $table->foreign('university_id')->references('id')->on('universities')->nullOnDelete();
            $table->unsignedBigInteger('research_center_id')->nullable()->index();
            $table->foreign('research_center_id')->references('id')->on('research_centers')->nullOnDelete();
            $table->unsignedBigInteger('campus_id')->nullable()->index();
            $table->foreign('campus_id')->references('id')->on('campuses')->nullOnDelete();
            $table->unsignedBigInteger('faculty_id')->nullable()->index();
            $table->foreign('faculty_id')->references('id')->on('faculties')->nullOnDelete();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};