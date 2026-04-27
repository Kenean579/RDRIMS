<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->integer('display_order')->default(0);
            $table->unsignedTinyInteger('status_id');
            $table->foreign('status_id')->references('id')->on('milestone_statuses');
            $table->timestamps();

            $table->index('project_id');
            $table->index('status_id');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
