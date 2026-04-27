<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('category_id');
            $table->foreign('category_id')->references('id')->on('output_categories')->restrictOnDelete();
            
            $table->unsignedTinyInteger('student_level_id')->nullable();
            $table->foreign('student_level_id')->references('id')->on('student_levels')->nullOnDelete();
            
            $table->unsignedTinyInteger('subtype_id');
            $table->foreign('subtype_id')->references('id')->on('output_subtypes')->restrictOnDelete();
            
            $table->foreignId('proposal_id')->nullable()->constrained('proposals')->nullOnDelete();
            $table->string('title', 255);
            $table->text('abstract');
            
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            
            $table->unsignedTinyInteger('status_id');
            $table->foreign('status_id')->references('id')->on('output_statuses');
            
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('feedback')->nullable();
            
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->decimal('budget', 12, 2)->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('student_level_id');
            $table->index('subtype_id');
            $table->index('proposal_id');
            $table->index('partner_id');
            $table->index('project_id');
            $table->index('status_id');
            $table->index('academic_year_id');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outputs');
    }
};
