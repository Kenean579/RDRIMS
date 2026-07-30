<?php

namespace Tests\Feature;

use App\Models\Call;
use App\Models\CallStatus;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * CallResourceTest
 *
 * Verifies that the API Resource correctly filters sensitive fields
 * and only exposes public business data.
 */
class CallResourceTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;
    protected CallStatus $openStatus;
    protected CallStatus $draftStatus;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test data
        $this->university = University::create(['name' => 'Test University', 'code' => 'TEST-UNI']);
        $this->openStatus = CallStatus::firstOrCreate(['name' => 'open']);
        $this->draftStatus = CallStatus::firstOrCreate(['name' => 'draft']);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'university_id' => $this->university->id,
        ]);
    }

    /**
     * Test that index endpoint uses CallResource and hides sensitive fields.
     */
    public function test_index_endpoint_uses_call_resource(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI,ML',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/calls');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'deadline',
                    'thematic_areas',
                    'status',
                    'creator',
                    'proposals_count',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
    }

    /**
     * Test that sensitive organizational fields are NOT exposed in list response.
     */
    public function test_index_does_not_expose_sensitive_fields(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/calls');
        $data = $response->json('data.0');

        // Sensitive organizational fields should NOT be exposed
        $this->assertArrayNotHasKey('university_id', $data);
        $this->assertArrayNotHasKey('campus_id', $data);
        $this->assertArrayNotHasKey('faculty_id', $data);
        $this->assertArrayNotHasKey('department_id', $data);
        $this->assertArrayNotHasKey('research_center_id', $data);

        // Internal fields should NOT be exposed
        $this->assertArrayNotHasKey('created_by', $data);
        $this->assertArrayNotHasKey('is_featured', $data);
        $this->assertArrayNotHasKey('is_public', $data);
        $this->assertArrayNotHasKey('metadata', $data);

        // Public business data SHOULD be exposed
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('deadline', $data);
        $this->assertArrayHasKey('creator', $data);
    }

    /**
     * Test that show endpoint uses CallResource and hides sensitive fields.
     */
    public function test_show_endpoint_uses_call_resource(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/calls/{$call->id}");

        $response->assertOk();
        $data = $response->json('data');

        // Sensitive fields should NOT be exposed
        $this->assertArrayNotHasKey('university_id', $data);
        $this->assertArrayNotHasKey('campus_id', $data);
        $this->assertArrayNotHasKey('created_by', $data);

        // Public business data SHOULD be exposed
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('creator', $data);
    }

    /**
     * Test that authenticated admin can see creator info but not raw user ID.
     */
    public function test_creator_field_contains_user_info_not_raw_id(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/calls/{$call->id}");

        $data = $response->json('data');
        $this->assertArrayHasKey('creator', $data);

        $creator = $data['creator'];
        $this->assertArrayHasKey('id', $creator);
        $this->assertArrayHasKey('name', $creator);
        $this->assertEquals($this->admin->id, $creator['id']);
        $this->assertEquals($this->admin->name, $creator['name']);
    }

    /**
     * Test that store endpoint returns CallResource wrapped response.
     */
    public function test_store_endpoint_uses_call_resource(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->postJson('/api/calls', [
            'title' => 'New Call',
            'description' => 'New Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'status_id' => $this->draftStatus->id,
        ]);

        $response->assertCreated();
        $data = $response->json('data');

        // Should return resource structure
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('creator', $data);

        // Should NOT expose sensitive fields
        $this->assertArrayNotHasKey('university_id', $data);
        $this->assertArrayNotHasKey('created_by', $data);
    }

    /**
     * Test that update endpoint returns CallResource wrapped response.
     */
    public function test_update_endpoint_uses_call_resource(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->draftStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->putJson("/api/calls/{$call->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);

        $response->assertOk();
        $data = $response->json('data');

        // Should return updated data in resource format
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('Updated Title', $data['title']);

        // Should NOT expose sensitive fields
        $this->assertArrayNotHasKey('university_id', $data);
        $this->assertArrayNotHasKey('created_by', $data);
    }

    /**
     * Test that resource exposes only allowed fields to unauthenticated users.
     */
    public function test_unauthenticated_users_see_limited_data(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/calls/{$call->id}");

        $response->assertOk();
        $data = $response->json('data');

        // Basic public data should be visible
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('deadline', $data);

        // Sensitive organizational data should NOT be visible
        $this->assertArrayNotHasKey('university_id', $data);
        $this->assertArrayNotHasKey('campus_id', $data);
    }

    /**
     * Test that status object structure is correct.
     */
    public function test_status_field_has_correct_structure(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/calls/{$call->id}");

        $data = $response->json('data');
        $this->assertArrayHasKey('status', $data);

        $status = $data['status'];
        $this->assertArrayHasKey('id', $status);
        $this->assertArrayHasKey('name', $status);
        $this->assertEquals($this->openStatus->id, $status['id']);
        $this->assertEquals('open', $status['name']);
    }

    /**
     * Test that proposals_count is present in response.
     */
    public function test_proposals_count_is_present(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/calls/{$call->id}");

        $data = $response->json('data');
        $this->assertArrayHasKey('proposals_count', $data);
        $this->assertIsInt($data['proposals_count']);
    }

    /**
     * Test that timestamps are in ISO 8601 format.
     */
    public function test_timestamps_are_iso_format(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->getJson("/api/calls/{$call->id}");

        $data = $response->json('data');

        // Check timestamp format (ISO 8601)
        $this->assertStringContainsString('T', $data['created_at']);
        $this->assertStringContainsString('Z', $data['created_at']);
    }
}
