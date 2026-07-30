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

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected University $university1;
    protected University $university2;
    protected Project $project1;
    protected Project $project2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two universities
        $this->university1 = University::factory()->create();
        $this->university2 = University::factory()->create();

        // Create users in different universities
        $this->user1 = User::factory()->create([
            'university_id' => $this->university1->id,
        ]);

        $this->user2 = User::factory()->create([
            'university_id' => $this->university2->id,
        ]);

        // Create projects
        $this->project1 = Project::factory()->create([
            'pi_id' => $this->user1->id,
        ]);

        $this->project2 = Project::factory()->create([
            'pi_id' => $this->user2->id,
        ]);

        // Seed lookup tables
        OutputStatus::firstOrCreate(['name' => 'draft']);
        OutputStatus::firstOrCreate(['name' => 'submitted']);
        OutputStatus::firstOrCreate(['name' => 'approved']);
        OutputCategory::firstOrCreate(['name' => 'research']);
        OutputSubtype::firstOrCreate(['name' => 'journal_article']);
        ParticipantType::firstOrCreate(['name' => 'student']);
    }

    public function test_cannot_view_cross_tenant_output(): void
    {
        $category = OutputCategory::first();
        $status = OutputStatus::first();
        $subtype = OutputSubtype::first();

        $output = Output::factory()->create([
            'project_id' => $this->project2->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);

        Sanctum::actingAs($this->user1);

        $response = $this->getJson("/api/outputs/{$output->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_update_cross_tenant_output(): void
    {
        $category = OutputCategory::first();
        $status = OutputStatus::first();
        $subtype = OutputSubtype::first();

        $output = Output::factory()->create([
            'project_id' => $this->project2->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);

        Sanctum::actingAs($this->user1);

        $response = $this->putJson("/api/outputs/{$output->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_cross_tenant_output(): void
    {
        $category = OutputCategory::first();
        $status = OutputStatus::where('name', 'draft')->first();
        $subtype = OutputSubtype::first();

        $output = Output::factory()->create([
            'project_id' => $this->project2->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);

        Sanctum::actingAs($this->user1);

        $response = $this->deleteJson("/api/outputs/{$output->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_submit_cross_tenant_output(): void
    {
        $category = OutputCategory::first();
        $status = OutputStatus::where('name', 'draft')->first();
        $subtype = OutputSubtype::first();
        $studentType = ParticipantType::first();

        $output = Output::factory()->create([
            'project_id' => $this->project2->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);

        $output->participants()->attach($this->user2->id, ['participant_type_id' => $studentType->id]);

        Sanctum::actingAs($this->user1);

        $response = $this->postJson("/api/outputs/{$output->id}/submit");

        $response->assertStatus(403);
    }

    public function test_user_cannot_verify_output(): void
    {
        $category = OutputCategory::first();
        $status = OutputStatus::first();
        $subtype = OutputSubtype::first();

        $output = Output::factory()->create([
            'project_id' => $this->project1->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);

        Sanctum::actingAs($this->user1);

        $response = $this->postJson("/api/outputs/{$output->id}/verify");

        $response->assertStatus(403);
    }

    public function test_list_respects_tenant_isolation(): void
    {
        $category = OutputCategory::first();
        $status = OutputStatus::first();
        $subtype = OutputSubtype::first();
        $studentType = ParticipantType::first();

        // Create outputs for both universities
        $output1 = Output::factory()->create([
            'project_id' => $this->project1->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);
        $output1->participants()->attach($this->user1->id, ['participant_type_id' => $studentType->id]);

        $output2 = Output::factory()->create([
            'project_id' => $this->project2->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);
        $output2->participants()->attach($this->user2->id, ['participant_type_id' => $studentType->id]);

        Sanctum::actingAs($this->user1);

        $response = $this->getJson('/api/outputs');

        $response->assertStatus(200);
        // User1 should only see their own outputs
        $data = $response->json('data');
        $ids = array_column($data, 'id');
        
        $this->assertContains($output1->id, $ids);
        $this->assertNotContains($output2->id, $ids);
    }

    public function test_non_participant_cannot_update_output(): void
    {
        $category = OutputCategory::first();
        $status = OutputStatus::where('name', 'draft')->first();
        $subtype = OutputSubtype::first();

        $output = Output::factory()->create([
            'project_id' => $this->project1->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);

        // user1 is not added as participant
        Sanctum::actingAs($this->user1);

        $response = $this->putJson("/api/outputs/{$output->id}", [
            'title' => 'New Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_participant_can_update_own_output(): void
    {
        $category = OutputCategory::first();
        $status = OutputStatus::where('name', 'draft')->first();
        $subtype = OutputSubtype::first();
        $studentType = ParticipantType::first();

        $output = Output::factory()->create([
            'project_id' => $this->project1->id,
            'category_id' => $category->id,
            'status_id' => $status->id,
            'subtype_id' => $subtype->id,
        ]);

        $output->participants()->attach($this->user1->id, ['participant_type_id' => $studentType->id]);

        Sanctum::actingAs($this->user1);

        $response = $this->putJson("/api/outputs/{$output->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
    }
}
