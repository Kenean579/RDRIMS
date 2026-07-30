<?php

namespace Tests\Feature\Ethics;

use App\Models\EthicsApprovalStatus;
use App\Models\EthicsRequest;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\ProposalType;
use App\Models\User;
use App\Models\University;
use App\Models\Call;
use App\Models\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $researcher1;
    protected User $researcher2;
    protected User $ethicsOfficer1;
    protected User $ethicsOfficer2;
    protected University $university1;
    protected University $university2;
    protected Proposal $proposal1;
    protected Proposal $proposal2;
    protected EthicsRequest $ethicsRequest1;
    protected EthicsRequest $ethicsRequest2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create universities
        $this->university1 = University::factory()->create();
        $this->university2 = University::factory()->create();

        // Create users for university 1
        $this->researcher1 = User::factory()->create([
            'university_id' => $this->university1->id,
        ]);
        $this->researcher1->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'researcher'])->id
        );

        $this->ethicsOfficer1 = User::factory()->create([
            'university_id' => $this->university1->id,
        ]);
        $this->ethicsOfficer1->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'ethics_officer'])->id
        );

        // Create users for university 2
        $this->researcher2 = User::factory()->create([
            'university_id' => $this->university2->id,
        ]);
        $this->researcher2->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'researcher'])->id
        );

        $this->ethicsOfficer2 = User::factory()->create([
            'university_id' => $this->university2->id,
        ]);
        $this->ethicsOfficer2->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'ethics_officer'])->id
        );

        // Create academic year and calls
        $academicYear = AcademicYear::factory()->create(['name' => '2025-2026']);
        $call1 = Call::factory()->create(['academic_year_id' => $academicYear->id]);
        $call2 = Call::factory()->create(['academic_year_id' => $academicYear->id]);

        // Create proposal statuses
        $peerReviewStatus = ProposalStatus::firstOrCreate(['name' => 'peer_review']);
        ProposalStatus::firstOrCreate(['name' => 'ethics_pending']);

        // Create proposals for each university
        $proposalType = ProposalType::firstOrCreate(['name' => 'research']);
        $this->proposal1 = Proposal::factory()->create([
            'submitted_by' => $this->researcher1->id,
            'call_id' => $call1->id,
            'status_id' => $peerReviewStatus->id,
            'type_id' => $proposalType->id,
        ]);

        $this->proposal2 = Proposal::factory()->create([
            'submitted_by' => $this->researcher2->id,
            'call_id' => $call2->id,
            'status_id' => $peerReviewStatus->id,
            'type_id' => $proposalType->id,
        ]);

        // Assign reviewers
        $this->proposal1->reviewers()->attach(
            $this->ethicsOfficer1->id,
            ['assigned_at' => now(), 'submitted_at' => now()]
        );
        $this->proposal2->reviewers()->attach(
            $this->ethicsOfficer2->id,
            ['assigned_at' => now(), 'submitted_at' => now()]
        );

        // Create ethics approval statuses
        $pendingStatus = EthicsApprovalStatus::firstOrCreate(['name' => 'pending']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'approved']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'rejected']);
        EthicsApprovalStatus::firstOrCreate(['name' => 'needs_revision']);

        // Create ethics requests
        $this->ethicsRequest1 = EthicsRequest::factory()->create([
            'proposal_id' => $this->proposal1->id,
            'approval_status_id' => $pendingStatus->id,
            'created_by' => $this->researcher1->id,
            'submitted_to_irb' => true,
        ]);

        $this->ethicsRequest2 = EthicsRequest::factory()->create([
            'proposal_id' => $this->proposal2->id,
            'approval_status_id' => $pendingStatus->id,
            'created_by' => $this->researcher2->id,
            'submitted_to_irb' => true,
        ]);
    }

    /**
     * Test: Multi-tenant isolation - Researcher cannot view other university's ethics request
     */
    public function test_researcher_cannot_view_other_university_ethics_request(): void
    {
        Sanctum::actingAs($this->researcher1);

        $response = $this->getJson("/api/ethics-requests/{$this->ethicsRequest2->id}");

        $response->assertForbidden();
    }

    /**
     * Test: Multi-tenant isolation - Ethics officer cannot view other university's ethics request
     */
    public function test_ethics_officer_cannot_view_other_university_ethics_request(): void
    {
        Sanctum::actingAs($this->ethicsOfficer1);

        $response = $this->getJson("/api/ethics-requests/{$this->ethicsRequest2->id}");

        $response->assertForbidden();
    }

    /**
     * Test: IDOR Prevention - Cannot update other user's ethics request
     */
    public function test_idor_cannot_update_other_user_ethics_request(): void
    {
        Sanctum::actingAs($this->researcher2);

        $response = $this->putJson("/api/ethics-requests/{$this->ethicsRequest1->id}", [
            'comments' => 'Trying to modify someone else\'s request',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test: IDOR Prevention - Cannot delete other user's ethics request
     */
    public function test_idor_cannot_delete_other_user_ethics_request(): void
    {
        Sanctum::actingAs($this->researcher2);

        $response = $this->deleteJson("/api/ethics-requests/{$this->ethicsRequest1->id}");

        $response->assertForbidden();
    }

    /**
     * Test: IDOR Prevention - Cannot approve other university's ethics request
     */
    public function test_idor_cannot_approve_other_university_ethics_request(): void
    {
        Sanctum::actingAs($this->ethicsOfficer2);

        $response = $this->postJson(
            "/api/ethics-requests/{$this->ethicsRequest1->id}/approve",
            ['comments' => 'Trying to approve from different institution']
        );

        $response->assertForbidden();
    }

    /**
     * Test: Privilege escalation prevention - Researcher cannot approve own request
     */
    public function test_privilege_escalation_researcher_cannot_approve_own_request(): void
    {
        Sanctum::actingAs($this->researcher1);

        $response = $this->postJson(
            "/api/ethics-requests/{$this->ethicsRequest1->id}/approve",
            ['comments' => 'Trying to approve own request']
        );

        $response->assertForbidden();
    }

    /**
     * Test: Privilege escalation prevention - Researcher cannot reject own request
     */
    public function test_privilege_escalation_researcher_cannot_reject_own_request(): void
    {
        Sanctum::actingAs($this->researcher1);

        $response = $this->postJson(
            "/api/ethics-requests/{$this->ethicsRequest1->id}/reject",
            ['comments' => 'Trying to reject own request']
        );

        $response->assertForbidden();
    }

    /**
     * Test: Protected field - Cannot directly update approval_status_id
     */
    public function test_cannot_directly_update_approval_status(): void
    {
        $approvedStatus = EthicsApprovalStatus::firstOrCreate(['name' => 'approved']);

        Sanctum::actingAs($this->researcher1);

        $response = $this->putJson("/api/ethics-requests/{$this->ethicsRequest1->id}", [
            'approval_status_id' => $approvedStatus->id,
            'reviewed_at' => now(),
        ]);

        $response->assertOk();

        // Verify that approval_status_id was not changed (was guarded)
        $this->assertDatabaseHas('ethics_requests', [
            'id' => $this->ethicsRequest1->id,
            'approval_status_id' => $this->ethicsRequest1->approval_status_id,
        ]);
    }

    /**
     * Test: Unauthenticated user cannot access ethics endpoints
     */
    public function test_unauthenticated_user_cannot_access_ethics(): void
    {
        $response = $this->getJson("/api/ethics-requests/{$this->ethicsRequest1->id}");

        $response->assertUnauthorized();
    }

    /**
     * Test: Multi-tenant isolation in list endpoint
     */
    public function test_list_endpoint_respects_multi_tenant_isolation(): void
    {
        // Create multiple ethics requests in both universities
        Sanctum::actingAs($this->researcher1);

        $response = $this->getJson('/api/ethics-requests');

        $response->assertOk();

        // All results should be from researcher1's university only
        $ethicsRequests = $response->json('data');
        
        foreach ($ethicsRequests as $ethics) {
            // Verify that the proposal belongs to researcher1's university
            $proposal = Proposal::find($ethics['proposal_id']);
            $this->assertEquals($this->researcher1->university_id, $proposal->submittedBy->university_id);
        }
    }

    /**
     * Test: Researcher cannot mark submitted if not owner
     */
    public function test_idor_cannot_mark_submitted_other_user_request(): void
    {
        Sanctum::actingAs($this->researcher2);

        $response = $this->postJson(
            "/api/ethics-requests/{$this->ethicsRequest1->id}/mark-submitted",
            []
        );

        $response->assertForbidden();
    }

    /**
     * Test: Ethics officer cannot approve if already reviewed
     */
    public function test_cannot_approve_already_reviewed_request(): void
    {
        $this->markTestSkipped('Proposal setup requires additional context');
    }
}
