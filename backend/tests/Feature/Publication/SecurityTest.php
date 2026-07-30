<?php

namespace Tests\Feature\Publication;

use App\Models\Project;
use App\Models\Publication;
use App\Models\PublicationStatus;
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

        // Seed publication statuses
        $this->seed(\Database\Seeders\PublicationStatusSeeder::class);
        $this->seed(\Database\Seeders\PublicationTypeSeeder::class);

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
    }

    public function test_cannot_view_cross_tenant_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project2->id, // Different university
        ]);

        $response = $this->getJson("/api/publications/{$publication->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_list_cross_tenant_publications(): void
    {
        Sanctum::actingAs($this->user1);

        // Create publications in both universities
        Publication::factory()->count(3)->create(['project_id' => $this->project1->id]);
        Publication::factory()->count(2)->create(['project_id' => $this->project2->id]);

        $response = $this->getJson('/api/publications');

        $response->assertStatus(200);
        // Should only see publications from own university
        $response->assertJsonCount(3, 'data');
    }

    public function test_cannot_update_cross_tenant_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project2->id,
        ]);

        $response = $this->putJson("/api/publications/{$publication->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_cross_tenant_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project2->id,
        ]);

        $response = $this->deleteJson("/api/publications/{$publication->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_add_author_to_cross_tenant_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project2->id,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/authors", [
            'user_id' => $this->user1->id,
            'author_order' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_submit_cross_tenant_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project2->id,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/submit");

        $response->assertStatus(403);
    }

    public function test_non_author_cannot_update_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project1->id,
        ]);

        // user1 is not an author, but same university
        $publication->authors()->create([
            'user_id' => User::factory()->create(['university_id' => $this->university1->id])->id,
            'author_order' => 1,
        ]);

        $response = $this->putJson("/api/publications/{$publication->id}", [
            'title' => 'New Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_published_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project1->id,
            'status_id' => PublicationStatus::where('name', 'published')->first()->id,
        ]);

        $publication->authors()->create([
            'user_id' => $this->user1->id,
            'author_order' => 1,
        ]);

        $response = $this->deleteJson("/api/publications/{$publication->id}");

        $response->assertStatus(403);
    }

    public function test_author_can_view_own_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project1->id,
        ]);

        $publication->authors()->create([
            'user_id' => $this->user1->id,
            'author_order' => 1,
        ]);

        $response = $this->getJson("/api/publications/{$publication->id}");

        $response->assertStatus(200);
    }

    public function test_author_can_update_draft_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project1->id,
            'status_id' => PublicationStatus::where('name', 'draft')->first()->id,
        ]);

        $publication->authors()->create([
            'user_id' => $this->user1->id,
            'author_order' => 1,
        ]);

        $response = $this->putJson("/api/publications/{$publication->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
    }

    public function test_cannot_update_published_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project1->id,
            'status_id' => PublicationStatus::where('name', 'published')->first()->id,
        ]);

        $publication->authors()->create([
            'user_id' => $this->user1->id,
            'author_order' => 1,
        ]);

        $response = $this->putJson("/api/publications/{$publication->id}", [
            'title' => 'Try to Update',
        ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_verify_publication(): void
    {
        Sanctum::actingAs($this->user1);

        $publication = Publication::factory()->create([
            'project_id' => $this->project1->id,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/verify");

        $response->assertStatus(403);
    }

    public function test_admin_can_verify_own_university_publication(): void
    {
        $admin = User::factory()->create([
            'university_id' => $this->university1->id,
        ]);
        $admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        Sanctum::actingAs($admin);

        $publication = Publication::factory()->create([
            'project_id' => $this->project1->id,
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/verify");

        $response->assertStatus(200);
    }

    public function test_admin_cannot_verify_cross_tenant_publication(): void
    {
        $admin = User::factory()->create([
            'university_id' => $this->university1->id,
        ]);
        $admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        Sanctum::actingAs($admin);

        $publication = Publication::factory()->create([
            'project_id' => $this->project2->id, // Different university
        ]);

        $response = $this->postJson("/api/publications/{$publication->id}/verify");

        $response->assertStatus(403);
    }

    public function test_statistics_scoped_to_university(): void
    {
        Sanctum::actingAs($this->user1);

        // Create publications in both universities
        Publication::factory()->count(3)->create(['project_id' => $this->project1->id]);
        Publication::factory()->count(5)->create(['project_id' => $this->project2->id]);

        $response = $this->getJson('/api/publications/statistics');

        $response->assertStatus(200);
        // Should only count publications from own university
        $response->assertJson(['total_publications' => 3]);
    }
}
