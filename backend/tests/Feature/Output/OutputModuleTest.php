<?php

namespace Tests\Feature\Output;

use App\Models\Output;
use App\Models\OutputCategory;
use App\Models\OutputStatus;
use App\Models\OutputSubtype;
use App\Models\ParticipantType;
use App\Models\Project;
use App\Models\User;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OutputModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected University $university;
    protected Project $project;
    protected OutputCategory $category;
    protected OutputStatus $draftStatus;
    protected OutputSubtype $subtype;
    protected ParticipantType $studentType;
    protected ParticipantType $supervisorType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create university
        $this->university = University::factory()->create();

        // Create users
        $this->user = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $this->user->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'researcher'])->id
        );

        $this->admin = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $this->admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        // Create project
        $this->project = Project::factory()->create([
            'pi_id' => $this->user->id,
        ]);

        // Seed output statuses, categories, types
        $this->draftStatus = OutputStatus::firstOrCreate(['name' => 'draft']);
        $submittedStatus = OutputStatus::firstOrCreate(['name' => 'submitted']);
        $approvedStatus = OutputStatus::firstOrCreate(['name' => 'approved']);
        $publishedStatus = OutputStatus::firstOrCreate(['name' => 'published']);
        $rejectedStatus = OutputStatus::firstOrCreate(['name' => 'rejected']);

        $this->category = OutputCategory::firstOrCreate(['name' => 'research']);
        OutputCategory::firstOrCreate(['name' => 'student']);

        $this->subtype = OutputSubtype::firstOrCreate(['name' => 'journal_article']);
        OutputSubtype::firstOrCreate(['name' => 'conference_paper']);
        OutputSubtype::firstOrCreate(['name' => 'dataset']);

        $this->studentType = ParticipantType::firstOrCreate(['name' => 'student']);
        $this->supervisorType = ParticipantType::firstOrCreate(['name' => 'supervisor']);
    }

    public function test_can_create_output(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/outputs', [
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'subtype_id' => $this->subtype->id,
            'title' => 'Test Output',
            'abstract' => 'Test abstract',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('outputs', [
            'title' => 'Test Output',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_can_list_outputs(): void
    {
        Sanctum::actingAs($this->user);

        Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $response = $this->getJson('/api/outputs');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    public function test_can_view_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        // Add user as participant
        $output->participants()->attach($this->user->id, ['participant_type_id' => $this->studentType->id]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/outputs/{$output->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => $output->title]);
    }

    public function test_can_update_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $output->participants()->attach($this->user->id, ['participant_type_id' => $this->studentType->id]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/outputs/{$output->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('outputs', [
            'id' => $output->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_draft_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $output->participants()->attach($this->user->id, ['participant_type_id' => $this->studentType->id]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/outputs/{$output->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('outputs', ['id' => $output->id]);
    }

    public function test_can_submit_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'status_id' => $this->draftStatus->id,
        ]);

        $output->participants()->attach($this->user->id, ['participant_type_id' => $this->studentType->id]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/outputs/{$output->id}/submit");

        $response->assertStatus(200);
        $submittedStatus = OutputStatus::where('name', 'submitted')->first();
        $this->assertDatabaseHas('outputs', [
            'id' => $output->id,
            'status_id' => $submittedStatus->id,
        ]);
    }

    public function test_cannot_submit_without_participants(): void
    {
        // Create output with user as creator
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'status_id' => $this->draftStatus->id,
        ]);

        // Add admin as participant so authorization passes
        // Admin can submit but business validation should still check for participants
        $output->participants()->attach($this->admin->id, ['participant_type_id' => $this->supervisorType->id]);
        
        // Now detach to test zero participants validation
        $output->participants()->detach();

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/outputs/{$output->id}/submit");

        $response->assertStatus(422);
    }

    public function test_admin_can_verify_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/outputs/{$output->id}/verify");

        $response->assertStatus(200);
        $this->assertDatabaseHas('outputs', [
            'id' => $output->id,
            'verified_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_approve_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/outputs/{$output->id}/approve");

        $response->assertStatus(200);
    }

    public function test_admin_can_reject_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/outputs/{$output->id}/reject", [
            'reason' => 'Does not meet quality standards'
        ]);

        $response->assertStatus(200);
    }

    public function test_cannot_update_verified_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'verified_by' => $this->admin->id,
            'verified_at' => now(),
        ]);

        $output->participants()->attach($this->user->id, ['participant_type_id' => $this->studentType->id]);

        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/outputs/{$output->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_verified_output(): void
    {
        $output = Output::factory()->create([
            'project_id' => $this->project->id,
            'category_id' => $this->category->id,
            'verified_by' => $this->admin->id,
            'verified_at' => now(),
        ]);

        $output->participants()->attach($this->user->id, ['participant_type_id' => $this->studentType->id]);

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/outputs/{$output->id}");

        $response->assertStatus(403);
    }
}
