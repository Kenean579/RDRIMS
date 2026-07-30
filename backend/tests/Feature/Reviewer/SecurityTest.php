<?php

namespace Tests\Feature\Reviewer;

use App\Models\Proposal;
use App\Models\ProposalReviewer;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesReviewerFixtures;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;
    use CreatesReviewerFixtures;

    protected University $otherUniversity;
    protected User $outsiderReviewer;
    protected User $outsiderAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReviewerFixtures();

        // Create another university
        $this->otherUniversity = University::create([
            'code' => 'OTHER',
            'name' => 'Other University',
            'location' => 'Other Location',
        ]);

        // Create users in other university
        $this->outsiderReviewer = User::create([
            'name' => 'Outsider Reviewer',
            'email' => 'outsider.reviewer@test.com',
            'password' => bcrypt('password'),
            'university_id' => $this->otherUniversity->id,
            'is_active' => true,
        ]);

        $this->outsiderAdmin = User::create([
            'name' => 'Outsider Admin',
            'email' => 'outsider.admin@test.com',
            'password' => bcrypt('password'),
            'university_id' => $this->otherUniversity->id,
            'is_active' => true,
        ]);

        $reviewerRole = \App\Models\Role::where('name', 'reviewer')->first();
        $adminRole = \App\Models\Role::where('name', 'research_admin')->first();

        $this->outsiderReviewer->roles()->attach($reviewerRole->id);
        $this->outsiderAdmin->roles()->attach($adminRole->id);
    }

    // ============================================================
    // ISSUE #1: Missing Authorization in SubmitReviewRequest
    // ============================================================

    public function test_unassigned_user_cannot_submit_review(): void
    {
        Sanctum::actingAs($this->outsiderReviewer);

        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        );

        $response->assertStatus(403);
    }

    public function test_non_reviewer_cannot_submit_review(): void
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@test.com',
            'password' => bcrypt('password'),
            'university_id' => $this->reviewerUser->university_id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($regularUser);

        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        );

        $response->assertStatus(403);
    }

    public function test_assigned_reviewer_can_submit_review(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        );

        $response->assertOk()
            ->assertJson(['message' => 'Review submitted.']);
    }

    // ============================================================
    // ISSUE #2: IDOR - Proposal Access Without Reviewer Assignment
    // ============================================================

    public function test_reviewer_cannot_access_unassigned_proposal(): void
    {
        $proposalType = \App\Models\ProposalType::firstOrCreate(['name' => 'research']);
        $proposalStatus = \App\Models\ProposalStatus::firstOrCreate(['name' => 'under_review']);
        
        // Use raw DB insert to bypass guarded fields
        $proposalId = \DB::table('proposals')->insertGetId([
            'call_id' => null,
            'type_id' => $proposalType->id,
            'title' => 'Other Proposal',
            'abstract' => 'Abstract',
            'objectives' => 'Objectives',
            'methodology' => 'Methodology',
            'keywords' => 'ai, health',
            'budget' => 1000,
            'status_id' => $proposalStatus->id,
            'submitted_by' => $this->submitterUser->id,
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherProposal = Proposal::find($proposalId);

        Sanctum::actingAs($this->reviewerUser);

        $response = $this->getJson("/api/reviewer/proposals/{$otherProposal->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Not assigned as reviewer for this proposal']);
    }

    public function test_reviewer_can_only_see_assigned_proposals(): void
    {
        $proposalType = \App\Models\ProposalType::firstOrCreate(['name' => 'research']);
        $proposalStatus = \App\Models\ProposalStatus::firstOrCreate(['name' => 'under_review']);
        
        // Use raw DB insert to bypass guarded fields
        $proposalId = \DB::table('proposals')->insertGetId([
            'call_id' => null,
            'type_id' => $proposalType->id,
            'title' => 'Other Proposal',
            'abstract' => 'Abstract',
            'objectives' => 'Objectives',
            'methodology' => 'Methodology',
            'keywords' => 'ai, health',
            'budget' => 1000,
            'status_id' => $proposalStatus->id,
            'submitted_by' => $this->submitterUser->id,
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherProposal = Proposal::find($proposalId);

        Sanctum::actingAs($this->reviewerUser);

        // Assign to first proposal only
        $response = $this->getJson('/api/reviewer/proposals');

        $data = $response->json('data');
        $ids = collect($data)->pluck('id')->all();

        $this->assertContains($this->proposal->id, $ids);
        $this->assertNotContains($otherProposal->id, $ids);
    }

    // ============================================================
    // ISSUE #3: Tenant Isolation Bypass
    // ============================================================

    public function test_outsider_reviewer_cannot_be_assigned(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson(
            "/api/proposals/{$this->proposal->id}/reviewers",
            ['reviewer_ids' => [$this->outsiderReviewer->id]]
        );

        $response->assertStatus(422);
    }

    public function test_admin_cannot_assign_from_other_university(): void
    {
        Sanctum::actingAs($this->outsiderAdmin);

        $response = $this->postJson(
            "/api/proposals/{$this->proposal->id}/reviewers",
            ['reviewer_ids' => [$this->reviewerUser->id]]
        );

        // Should fail authorization check
        $response->assertStatus(403);
    }

    public function test_proposal_reviewers_list_respects_tenant_scope(): void
    {
        Sanctum::actingAs($this->outsiderAdmin);

        $response = $this->getJson("/api/proposals/{$this->proposal->id}/reviewers");

        // Admin from different university should not access
        $response->assertStatus(403);
    }

    // ============================================================
    // ISSUE #4: Excel Import Authorization Bypass
    // ============================================================

    public function test_unassigned_reviewer_cannot_download_template(): void
    {
        Sanctum::actingAs($this->outsiderReviewer);

        $response = $this->getJson(
            "/api/reviewer/proposals/{$this->proposal->id}/template"
        );

        $response->assertStatus(403);
    }

    public function test_unassigned_reviewer_cannot_import_review(): void
    {
        Sanctum::actingAs($this->outsiderReviewer);

        $file = \Illuminate\Http\UploadedFile::fake()->create('review.xlsx');

        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/import",
            ['file' => $file]
        );

        $response->assertStatus(403);
    }

    public function test_excel_import_rejects_mismatched_proposal(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $proposalType = \App\Models\ProposalType::firstOrCreate(['name' => 'research']);
        $proposalStatus = \App\Models\ProposalStatus::firstOrCreate(['name' => 'under_review']);
        
        // Create another proposal using raw DB insert
        $proposalId = \DB::table('proposals')->insertGetId([
            'call_id' => null,
            'type_id' => $proposalType->id,
            'title' => 'Other Proposal',
            'abstract' => 'Abstract',
            'objectives' => 'Objectives',
            'methodology' => 'Methodology',
            'keywords' => 'ai, health',
            'budget' => 1000,
            'status_id' => $proposalStatus->id,
            'submitted_by' => $this->submitterUser->id,
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherProposal = Proposal::find($proposalId);

        // Create template for wrong proposal
        $file = $this->createFakeExcelFile($otherProposal, $this->reviewerUser);

        // Try to import to original proposal
        $response = $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/import",
            ['file' => $file]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    // ============================================================
    // ISSUE #7: Review Data Leakage in ProposalResource
    // ============================================================

    public function test_blind_review_hides_submitter_information(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $response = $this->getJson("/api/reviewer/proposals/{$this->proposal->id}");

        $response->assertOk()
            ->assertJsonPath('submitted_by.id', null)
            ->assertJsonPath('submitted_by.name', null);
    }

    public function test_blind_review_hides_investigators(): void
    {
        Sanctum::actingAs($this->reviewerUser);

        $response = $this->getJson("/api/reviewer/proposals/{$this->proposal->id}");

        $response->assertOk();
        
        $data = $response->json();
        $this->assertEmpty($data['investigators'] ?? []);
    }

    public function test_reviewer_cannot_see_other_reviewers_scores_before_publication(): void
    {
        // Assign second reviewer
        $secondReviewer = User::create([
            'name' => 'Second Reviewer',
            'email' => 'second@test.com',
            'password' => bcrypt('password'),
            'university_id' => $this->reviewerUser->university_id,
            'is_active' => true,
        ]);

        $reviewerRole = \App\Models\Role::where('name', 'reviewer')->first();
        $secondReviewer->roles()->attach($reviewerRole->id);

        $this->proposal->reviewers()->attach($secondReviewer->id, [
            'assigned_by' => $this->adminUser->id,
            'assigned_at' => now(),
        ]);

        // First reviewer submits
        Sanctum::actingAs($this->reviewerUser);
        $this->postJson(
            "/api/reviewer/proposals/{$this->proposal->id}/review",
            $this->validReviewPayload()
        )->assertOk();

        // Second reviewer should not see first reviewer's scores
        Sanctum::actingAs($secondReviewer);
        $response = $this->getJson("/api/reviewer/proposals/{$this->proposal->id}");

        $data = $response->json();
        
        // Verify own review data is accessible
        $this->assertArrayHasKey('reviewPivot', $data);
        $this->assertNull($data['reviewPivot']['overall_score']);
    }

    // ============================================================
    // ISSUE #9: ProposalReviewer Timestamps Not Immutable
    // ============================================================

    public function test_submitted_at_cannot_be_mass_assigned(): void
    {
        // Create a new reviewer for this test to avoid duplicate constraint
        $newReviewer = User::create([
            'name' => 'New Test Reviewer',
            'email' => 'new-reviewer-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'university_id' => $this->reviewerUser->university_id,
            'is_active' => true,
        ]);
        
        $reviewerRole = \App\Models\Role::firstOrCreate(['name' => 'reviewer']);
        $newReviewer->roles()->attach($reviewerRole->id, ['assigned_at' => now()]);
        
        // This should NOT be allowed to create with submitted_at
        $assignment = ProposalReviewer::create([
            'proposal_id' => $this->proposal->id,
            'reviewer_id' => $newReviewer->id,
            'assigned_by' => $this->adminUser->id,
            'assigned_at' => now(),
            'submitted_at' => now(), // This should be ignored due to mass assignment protection
        ]);

        // Verify submitted_at was NOT set (it's not in fillable array)
        $this->assertNull($assignment->submitted_at);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    private function createFakeExcelFile(Proposal $proposal, User $reviewer)
    {
        // Create a minimal valid Excel file for testing
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('G2', $proposal->id);
        $sheet->setCellValue('H2', $reviewer->id);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return new \Illuminate\Http\UploadedFile(
            $tmpFile,
            'test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
