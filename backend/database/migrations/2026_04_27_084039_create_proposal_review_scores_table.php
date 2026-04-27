<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_review_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_reviewer_id')->constrained('proposal_reviewers')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('review_criteria')->cascadeOnDelete();
            $table->integer('score');
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['proposal_reviewer_id', 'criterion_id'], 'prop_rev_score_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_review_scores');
    }
};
