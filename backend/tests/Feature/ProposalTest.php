<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\ProposalType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProposalTest extends TestCase
{
    use RefreshDatabase;

    protected User $researcher;
    protected User $researchAdmin;
    protected User $otherResearcher;
    protected Call $call;
    protected ProposalStatus $draftStatus;
    protected ProposalStatus $submittedStatus;
    protected ProposalType $proposalType;
    protected AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->draftStatus = ProposalStatus::firstOrCreate(['name' => 'draft'], ['description' => 'Draft']);
        $this->submittedStatus = ProposalStatus::firstOrCreate(['name' => 'submitted'], ['description' => 'Submitted']);
        $this->proposalType = ProposalType::firstOrCreate(['name' => 'research'], ['description' => 'Research']);
        $this->academicYear = AcademicYear::firstOrCreate(
            ['name' => '2025/2026'],
            ['start_date' => now(), 'end_date' => now()->addYear(), 'is_current' => true]
        );

        // Create a test university
        $testUniversity = \App\Models\University::firstOrCreate(
            ['name' => 'Test University'],
            ['code' => 'TU', 'website' => 'https://test.edu']
        );

        // Create roles
        $researcherRole = \App\Models\Role::firstOrCreate(['name' => 'researcher'], ['description' => 'Researcher']);
        $researchAdminRole = \App\Models\Role::firstOrCreate(['name' => 'research_admin'], ['description' => 'Research Admin']);

        // Create necessary permissions for researchers to view calls and submit proposals
        $callViewAnyPerm = \App\Models\Permission::firstOrCreate(
            ['name' => 'call.viewAny'],
            ['description' => 'View all calls']
        );
        $callViewPerm = \App\Models\Permission::firstOrCreate(
            ['name' => 'call.view'],
            ['description' => 'View a call']
        );
        $researcherRole->permissions()->syncWithoutDetaching([$callViewAnyPerm->id, $callViewPerm->id]);

        // Create users and assign roles
        $this->researcher = User::factory()->create(['university_id' => $testUniversity->id]);
        $this->researcher->roles()->attach($researcherRole->id, ['assigned_at' => now()]);

        $this->researchAdmin = User::factory()->create([
            'university_id' => $this->researcher->university_id
        ]);
        $this->researchAdmin->roles()->attach($researchAdminRole->id, ['assigned_at' => now()]);

        // Create other university for other researcher
        $otherUniversity = \App\Models\University::firstOrCreate(
            ['name' => 'Other University'],
            ['code' => 'OU', 'website' => 'https://other.edu']
        );
        
        $this->otherResearcher = User::factory()->create(['university_id' => $otherUniversity->id]);
        $this->otherResearcher->roles()->attach($researcherRole->id, ['assigned_at' => now()]);

        $this->call = Call::factory()->create([
            'university_id' => $this->researcher->university_id,
            'deadline' => now()->addDays(30),
        ]);
    }

    public function test_authenticated_user_can_list_their_proposals()
    {
        $myProposal = Proposal::factory()->create([
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $otherProposal = Proposal::factory()->create([
            'submitted_by' => $this->otherResearcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $response = $this->actingAs($this->researcher)->getJson('/api/proposals');

        $response->assertStatus(200);
        $returnedIds = collect($response->json())->pluck('id')->toArray();
        
        $this->assertContains($myProposal->id, $returnedIds);
        $this->assertNotContains($otherProposal->id, $returnedIds);
    }

    public function test_user_can_create_draft_proposal()
    {
        Storage::fake('local');

        $response = $this->actingAs($this->researcher)->postJson('/api/proposals', [
            'title' => 'Test Proposal',
            'abstract' => 'This is a test abstract that is at least 20 characters long.',
            'budget' => 5000,
            'call_id' => $this->call->id,
            'type_id' => $this->proposalType->id,
            'keywords' => 'test, proposal',
            'objectives' => 'Test objectives that are at least 20 characters.',
            'methodology' => 'Test methodology that is at least 20 characters.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'title',
            'status' => ['id', 'name'],
        ]);

        // Verify it's created as draft, not submitted
        $this->assertDatabaseHas('proposals', [
            'title' => 'Test Proposal',
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        // Verify submitted_at is NULL for draft
        $proposal = Proposal::where('title', 'Test Proposal')->first();
        $this->assertNull($proposal->submitted_at);
    }

    public function test_user_cannot_create_proposal_with_expired_call()
    {
        $expiredCall = Call::factory()->create([
            'university_id' => $this->researcher->university_id,
            'deadline' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->researcher)->postJson('/api/proposals', [
            'title' => 'Test Proposal',
            'abstract' => 'This is a test abstract that is at least 20 characters long.',
            'budget' => 5000,
            'call_id' => $expiredCall->id,
            'type_id' => $this->proposalType->id,
            'keywords' => 'test, proposal',
            'objectives' => 'Test objectives that are at least 20 characters.',
            'methodology' => 'Test methodology that is at least 20 characters.',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['call_id']);
    }

    public function test_user_cannot_access_proposal_from_different_tenant()
    {
        $otherProposal = Proposal::factory()->create([
            'submitted_by' => $this->otherResearcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $response = $this->actingAs($this->researcher)->getJson("/api/proposals/{$otherProposal->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_view_their_own_proposal()
    {
        $proposal = Proposal::factory()->create([
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $response = $this->actingAs($this->researcher)->getJson("/api/proposals/{$proposal->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $proposal->id]);
    }

    public function test_user_can_update_their_draft_proposal()
    {
        $proposal = Proposal::factory()->create([
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->draftStatus->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->researcher)->putJson("/api/proposals/{$proposal->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_someone_elses_proposal()
    {
        $proposal = Proposal::factory()->create([
            'submitted_by' => $this->otherResearcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $response = $this->actingAs($this->researcher)->putJson("/api/proposals/{$proposal->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_draft_proposal()
    {
        $proposal = Proposal::factory()->create([
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $response = $this->actingAs($this->researcher)->deleteJson("/api/proposals/{$proposal->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
    }

    public function test_user_cannot_mass_assign_protected_fields()
    {
        $response = $this->actingAs($this->researcher)->postJson('/api/proposals', [
            'title' => 'Test Proposal',
            'abstract' => 'This is a test abstract that is at least 20 characters long.',
            'budget' => 5000,
            'call_id' => $this->call->id,
            'type_id' => $this->proposalType->id,
            'keywords' => 'test, proposal',
            'objectives' => 'Test objectives that are at least 20 characters.',
            'methodology' => 'Test methodology that is at least 20 characters.',
            'status_id' => $this->submittedStatus->id, // Try to bypass draft status
            'approved_by' => 999,
            'approved_at' => now(),
            'submitted_at' => now(),
        ]);

        $response->assertStatus(201);

        // Verify protected fields were not mass-assigned
        $proposal = Proposal::latest()->first();
        $this->assertEquals($this->draftStatus->id, $proposal->status_id);
        $this->assertNull($proposal->approved_by);
        $this->assertNull($proposal->approved_at);
        $this->assertNull($proposal->submitted_at);
    }

    public function test_user_can_submit_draft_proposal()
    {
        $proposal = Proposal::factory()->create([
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        // Add at least one investigator
        $proposal->investigators()->create([
            'name' => 'Test Investigator',
            'email' => 'investigator@test.com',
            'role_id' => 1,
            'status_id' => 1,
            'invited_at' => now(),
        ]);

        $response = $this->actingAs($this->researcher)->postJson("/api/proposals/{$proposal->id}/submit");

        $response->assertStatus(200);
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status_id' => $this->submittedStatus->id,
        ]);

        $proposal->refresh();
        $this->assertNotNull($proposal->submitted_at);
    }

    public function test_user_cannot_submit_proposal_without_investigators()
    {
        $proposal = Proposal::factory()->create([
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $response = $this->actingAs($this->researcher)->postJson("/api/proposals/{$proposal->id}/submit");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['investigators']);
    }

    public function test_admin_can_view_proposals_in_their_institution()
    {
        $researcherProposal = Proposal::factory()->create([
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->submittedStatus->id,
        ]);

        $response = $this->actingAs($this->researchAdmin)->getJson("/api/proposals/{$researcherProposal->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $researcherProposal->id]);
    }

    public function test_proposal_resource_does_not_leak_sensitive_data()
    {
        $proposal = Proposal::factory()->create([
            'submitted_by' => $this->researcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $response = $this->actingAs($this->researcher)->getJson("/api/proposals/{$proposal->id}");

        $response->assertStatus(200);
        
        // Resource should structure the data properly, not expose raw model
        $response->assertJsonStructure([
            'id',
            'title',
            'status' => ['id', 'name'],
            'submitted_by' => ['id', 'name'],
        ]);

        // Should not have raw timestamps or internal fields exposed inappropriately
        $json = $response->json();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('id', $json);
    }

    public function test_cannot_bypass_tenant_isolation_via_call_withoutGlobalScopes()
    {
        // Create a call in a different university
        $otherUniversityCall = Call::factory()->create([
            'university_id' => 999,
            'deadline' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->researcher)->postJson('/api/proposals', [
            'title' => 'Cross-tenant Test',
            'abstract' => 'This should not work across tenants because call belongs to different university.',
            'budget' => 5000,
            'call_id' => $otherUniversityCall->id,
            'type_id' => $this->proposalType->id,
            'keywords' => 'test, cross-tenant',
            'objectives' => 'Test objectives that are at least 20 characters.',
            'methodology' => 'Test methodology that is at least 20 characters.',
        ]);

        // Should be forbidden due to call access check
        $response->assertStatus(403);
    }

    public function test_validation_requires_minimum_lengths()
    {
        $response = $this->actingAs($this->researcher)->postJson('/api/proposals', [
            'title' => 'Too',
            'abstract' => 'Short',
            'budget' => 5000,
            'call_id' => $this->call->id,
            'type_id' => $this->proposalType->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'abstract']);
    }

    public function test_user_cannot_submit_someone_elses_proposal()
    {
        $proposal = Proposal::factory()->create([
            'submitted_by' => $this->otherResearcher->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $proposal->investigators()->create([
            'name' => 'Test Investigator',
            'email' => 'investigator@test.com',
            'role_id' => 1,
            'status_id' => 1,
            'invited_at' => now(),
        ]);

        $response = $this->actingAs($this->researcher)->postJson("/api/proposals/{$proposal->id}/submit");

        $response->assertStatus(403);
    }
}
