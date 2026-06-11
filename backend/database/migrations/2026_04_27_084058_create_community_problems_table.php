<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_problems', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->string('location', 255);
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contact_info', 255)->nullable();
            $table->unsignedBigInteger('research_center_id')->nullable();
            $table->foreign('research_center_id')->references('id')->on('research_centers')->nullOnDelete();
            $table->unsignedTinyInteger('status_id');
            $table->foreign('status_id')->references('id')->on('community_problem_statuses')->restrictOnDelete();
            $table->foreignId('claimed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('claimed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('linked_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->text('feedback')->nullable();
            $table->tinyInteger('rating')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();

            $table->index(['status_id', 'submitted_by', 'claimed_by', 'location'], 'cp_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_problems');
    }
};
