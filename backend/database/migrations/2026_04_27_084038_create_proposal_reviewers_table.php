<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at');
            $table->dateTime('submitted_at')->nullable()->index();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('overall_comments')->nullable();
            
            $table->unsignedTinyInteger('decision_id')->nullable()->index();
            $table->foreign('decision_id')->references('id')->on('review_decisions')->nullOnDelete();

            $table->timestamps();

            $table->unique(['proposal_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_reviewers');
    }
};
