<?php

namespace Tests\Unit;

use App\Models\ProposalReviewer;
use App\Models\ReviewCriterion;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesReviewerFixtures;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesReviewerFixtures;

    private ReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReviewerFixtures();
        $this->service = app(ReviewService::class);
    }

    public function test_validate_scores_rejects_values_above_max(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->validateScores([
            ['criterion_id' => $this->criterion->id, 'score' => 50],
        ]);
    }

    public function test_assert_not_locked_throws_for_submitted_review(): void
    {
        $this->assignment->update(['submitted_at' => now()]);

        $this->expectException(ValidationException::class);

        $this->service->assertNotLocked($this->assignment->fresh());
    }

    public function test_get_proposal_review_progress_counts_assignments(): void
    {
        $progress = $this->service->getProposalReviewProgress($this->proposal);

        $this->assertSame(1, $progress['assigned']);
        $this->assertSame(0, $progress['completed']);
        $this->assertSame(1, $progress['pending']);
    }

    public function test_reopen_clears_submitted_at(): void
    {
        $this->assignment->update([
            'submitted_at' => now(),
            'overall_score' => 4.5,
            'decision_id' => $this->decision->id,
        ]);

        $reopened = $this->service->reopenReview($this->assignment->fresh(), $this->adminUser);

        $this->assertNull($reopened->submitted_at);
    }
}
