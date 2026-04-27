<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->nullable()->constrained('proposals')->nullOnDelete();
            $table->string('title', 255);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_budget', 12, 2);
            $table->json('budget_allocation')->nullable();
            $table->unsignedBigInteger('cover_image_id')->nullable()->index();
            $table->unsignedTinyInteger('status_id');
            $table->foreign('status_id')->references('id')->on('project_statuses');
            $table->foreignId('pi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->timestamps();

            $table->index('proposal_id');
            $table->index('status_id');
            $table->index('pi_id');
            $table->index('academic_year_id');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
