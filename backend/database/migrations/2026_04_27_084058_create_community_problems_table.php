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
            $table->unsignedTinyInteger('status_id');
            $table->foreign('status_id')->references('id')->on('community_problem_statuses');
            $table->foreignId('claimed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('claimed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('linked_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->text('feedback')->nullable();
            $table->tinyInteger('rating')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();

            $table->index('status_id');
            $table->index('submitted_by');
            $table->index('claimed_by');
            $table->index('linked_project_id');
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_problems');
    }
};
