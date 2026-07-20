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
            $table->foreignId('guideline_file_id')
                                ->nullable()
                                ->constrained('files')
                                ->nullOnDelete();
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

            //time stamps..........
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('opens_at')->nullable()->index();
            $table->timestamp('closes_at')->nullable()->index();

            //meta data and for featured......... 
            // we can add extra fields to this table without changing the schema 
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->json('metadata')->nullable();
            
            //softdeletes 
            $table->softDeletes();
            //timestamps
            $table->timestamps();
            
            

// for dashboard performance  -> indexing on status_id and university_id etc
// these are composite indexes for fast retrieval of data for admin dashboard
            $table->index(['university_id', 'status_id']);
            $table->index(['campus_id', 'status_id']);
            $table->index(['faculty_id', 'status_id']);
            $table->index(['department_id', 'status_id']);
            $table->index(['research_center_id', 'status_id']);  

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};