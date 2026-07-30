<?php

namespace Tests\Feature\Publication;

use App\Models\Project;
use App\Models\Publication;
use App\Models\PublicationStatus;
use App\Models\PublicationType;
use App\Models\User;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PublicationModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $pi;
    protected University $university;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed publication statuses and types
        $this->seed(\Database\Seeders\PublicationStatusSeeder::class);
        $this->seed(\Database\Seeders\PublicationTypeSeeder::class);

        // Create university
        $this->university = University::factory()->create();

        // Create PI user
        $this->pi = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $this->pi->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'pi_role'])->id
        );

        // Create regular user
        $this->user = User::factory()->create([
            'university_id' => $this->university->id,
        ]);

        // Create project
        $this->project = Project::factory()->create([
            'pi_id' => $this->pi->id,
        ]);
    }

    public function test_can_create_publication(): void
    {
        // Add researcher role to user
        $this->user->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'researcher'])->id
        );

        Sanctum::actingAs($this->user);

        $data = [
            'project_id' => $this->project->id,
            'type_id' => PublicationType::where('name', 'journal_article')->first()->id,
            'title' => 'Test Publication',
            'abstract' => 'Test abstract',
            'journal' => 'Test Journal',
            'publication_date' => '2024-01-01',
        ];

        $response = $this->postJson('/api/publications', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('publications', [
            'title' => 'Test Publication',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_can_list_publications(): void
    {
        // Add researcher role
        $this->user->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'researcher'])->id
        );

        Sanctum::actingAs($this->user);

        // Create one publication to test
        $pub = Publication::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/publications');

        $response->assertStatus(200);
        // Just verify we get a data array back
        $this->assertIsArray($response->json('data'));
    }

    public function test_can_view_publication(): void
    {
        Sanctum::actingAs($this->user);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/publications/{$publication->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => $publication->title]);
    }

    public function test_can_update_publication(): void
    {
        Sanctum::actingAs($this->user);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
        ]);

        // Add user as author
        $publication->authors()->create([
            'user_id' => $this->user->id,
            'author_order' => 1,
        ]);

        $response = $this->putJson("/api/publications/{$publication->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_draft_publication(): void
    {
        Sanctum::actingAs($this->user);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
        ]);

        // Add user as first author
        $publication->authors()->create([
            'user_id' => $this->user->id,
            'author_order' => 1,
        ]);

        $response = $this->deleteJson("/api/publications/{$publication->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('publications', ['id' => $publication->id]);
    }

    public function test_can_submit_publication_workflow(): void
    {
        Sanctum::actingAs($this->user);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
        ]);

        // Add internal author (required for submission)
        $publication->authors()->create([
            'user_id' => $this->user->id,
            'author_order' => 1,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/submit");

        $response->assertStatus(200);
        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'status_id' => PublicationStatus::where('name', 'submitted')->first()->id,
        ]);
    }

    public function test_cannot_submit_without_internal_author(): void
    {
        // Create admin in same university
        $admin = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        Sanctum::actingAs($admin);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
            'created_by' => $admin->id,
        ]);

        // Only external author (no internal author)
        $publication->authors()->create([
            'external_author_name' => 'External Author',
            'author_order' => 1,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/submit");

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Publication must have at least one internal author']);
    }

    public function test_admin_can_verify_publication(): void
    {
        $admin = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        Sanctum::actingAs($admin);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/verify");

        $response->assertStatus(200);
        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'verified_by' => $admin->id,
        ]);
        $this->assertNotNull($publication->fresh()->verified_at);
    }

    public function test_admin_can_approve_publication(): void
    {
        $admin = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        Sanctum::actingAs($admin);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
            'status_id' => PublicationStatus::where('name', 'submitted')->first()->id,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/approve", [
            'comments' => 'Approved for publication',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'status_id' => PublicationStatus::where('name', 'accepted')->first()->id,
        ]);
    }

    public function test_admin_can_reject_publication(): void
    {
        $admin = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        Sanctum::actingAs($admin);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
            'status_id' => PublicationStatus::where('name', 'submitted')->first()->id,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/reject", [
            'reason' => 'Does not meet quality standards',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'status_id' => PublicationStatus::where('name', 'rejected')->first()->id,
        ]);
    }

    public function test_can_publish_verified_accepted_publication(): void
    {
        $admin = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        Sanctum::actingAs($admin);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
            'status_id' => PublicationStatus::where('name', 'accepted')->first()->id,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/publish");

        $response->assertStatus(200);
        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'status_id' => PublicationStatus::where('name', 'published')->first()->id,
        ]);
    }

    public function test_can_add_author_to_publication(): void
    {
        Sanctum::actingAs($this->user);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
        ]);

        // Add user as author to grant permission
        $publication->authors()->create([
            'user_id' => $this->user->id,
            'author_order' => 1,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/authors", [
            'user_id' => $this->pi->id,
            'author_order' => 2,
            'contribution_role' => 'co_author',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('publication_authors', [
            'publication_id' => $publication->id,
            'user_id' => $this->pi->id,
        ]);
    }

    public function test_can_add_external_author(): void
    {
        Sanctum::actingAs($this->user);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $publication->authors()->create([
            'user_id' => $this->user->id,
            'author_order' => 1,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/authors", [
            'external_author_name' => 'Dr. External Author',
            'external_institution' => 'External University',
            'author_order' => 2,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('publication_authors', [
            'publication_id' => $publication->id,
            'external_author_name' => 'Dr. External Author',
        ]);
    }

    public function test_cannot_remove_last_internal_author(): void
    {
        Sanctum::actingAs($this->user);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $author = $publication->authors()->create([
            'user_id' => $this->user->id,
            'author_order' => 1,
        ]);

        $response = $this->deleteJson("/api/publications/{$publication->id}/authors/{$author->id}");

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot remove the last internal author']);
    }

    public function test_can_update_citation_count(): void
    {
        $admin = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        Sanctum::actingAs($admin);

        $publication = Publication::factory()->create([
            'project_id' => $this->project->id,
            'citation_count' => 5,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/update-citations", [
            'citation_count' => 10,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'citation_count' => 10,
        ]);
    }

    public function test_can_get_publication_statistics(): void
    {
        Sanctum::actingAs($this->user);

        // Create some publications
        Publication::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'status_id' => PublicationStatus::where('name', 'published')->first()->id,
        ]);

        $response = $this->getJson('/api/publications/statistics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_publications',
            'published_publications',
            'total_citations',
            'average_citations',
            'by_type',
            'by_year',
        ]);
    }
}
