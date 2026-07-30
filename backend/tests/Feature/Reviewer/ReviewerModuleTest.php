<?php

namespace Tests\Feature\Reviewer;

use App\Models\AuditLog;
use App\Models\ProposalReviewer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesReviewerFixtures;
use Tests\TestCase;

class ReviewerModuleTest extends TestCase
{
    use RefreshDatabase;
    use CreatesReviewerFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReviewerFixtures();
    }

    public function test_reviewer_can_submit_review(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        );

        $response->assertOk()
            ->assertJson(['message' => 'Review submitted.']);

        $this->assertNotNull(
            ProposalReviewer::find($this->assignment->id)->submitted_at
        );
    }

    public function test_duplicate_submission_is_rejected(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        )->assertOk();

        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['review']);
    }

    public function test_score_above_max_is_rejected(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $payload = $this->validReviewPayload();
        $payload['scores'][0]['score'] = 99;

        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $payload
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scores.0.score']);
    }

    public function test_unassigned_user_cannot_submit_review(): void
    {
        $outsider = \App\Models\User::create([
            'name' => 'Outsider',
            'email' => 'outsider@test.com',
            'password' => bcrypt('password'),
            'university_id' => \App\Models\University::first()?->id ?? 1,
            'is_active' => true,
        ]);

        Sanctum::actingAs($outsider);

        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        );

        // Authorization fails at FormRequest level, returning 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_research_admin_can_reopen_locked_review(): void
    {
        Sanctum::actingAs($this->reviewerUser);
        $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        )->assertOk();

        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson(
            "/api/proposals/{$this->proposal->id}/reviewers/{$this->reviewerUser->id}/reopen"
        );

        $response->assertOk()
            ->assertJson(['message' => 'Review reopened for revision.']);

        $this->assertNull(
            ProposalReviewer::find($this->assignment->id)->submitted_at
        );
    }

    public function test_reviewer_cannot_reopen_locked_review(): void
    {
        Sanctum::actingAs($this->reviewerUser);
        $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        )->assertOk();

        $response = $this->postJson(
            "/api/proposals/{$this->proposal->id}/reviewers/{$this->reviewerUser->id}/reopen"
        );

        $response->assertStatus(403);
    }

    public function test_submit_review_creates_audit_log(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        )->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'reviewer_submit_review',
            'table_name' => 'proposals',
            'record_id' => $this->proposal->id,
            'user_id' => $this->reviewerUser->id,
        ]);
    }

    public function test_blind_review_hides_submitter_on_show(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $response = $this->getJson("/api/reviewer/proposals/{$this->proposal->id}");

        $response->assertOk()
            ->assertJsonPath('submitted_by.id', null)
            ->assertJsonPath('submitted_by.name', null)
            ->assertJsonPath('is_locked', false)
            ->assertJsonStructure(['reviewPivot' => ['submitted_at', 'scores']]);
    }

    public function test_reviewer_dashboard_returns_progress_stats(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'assigned_reviews',
                'pending_reviews',
                'completed_reviews',
                'average_score',
            ])
            ->assertJsonPath('assigned_reviews', 1)
            ->assertJsonPath('pending_reviews', 1);
    }

    public function test_research_admin_reviewer_list_includes_review_progress(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/reviewer/proposals');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['review_progress' => ['assigned', 'completed', 'pending', 'average_score']],
                ],
            ]);
    }
}
