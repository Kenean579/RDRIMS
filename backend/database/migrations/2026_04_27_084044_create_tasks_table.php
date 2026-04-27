<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained('milestones')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description');
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date');
            $table->unsignedTinyInteger('status_id');
            $table->foreign('status_id')->references('id')->on('task_statuses');
            $table->timestamps();

            $table->index('milestone_id');
            $table->index('assigned_to');
            $table->index('status_id');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
