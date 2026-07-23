<?php

namespace Tests\Feature;

use App\Models\Call;
use App\Models\CallStatus;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * ApiResourceTest
 * 
 * Verifies that the CallResource correctly hides sensitive fields
 * in all API endpoints.
 */
class ApiResourceTest extends TestCase
{
    use RefreshDatabase;

    protected University $university;
    protected User $admin;
    protected CallStatus $openStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::firstOrCreate(['name' => 'research_admin']);
        Role::firstOrCreate(['name' => 'super_admin']);

        // Seed status
        $this->openStatus = CallStatus::firstOrCreate(['name' => 'open']);
        CallStatus::firstOrCreate(['name' => 'draft']);
        CallStatus::firstOrCreate(['name' => 'closed']);

        // Create university
        $this->university = University::create([
            'name' => 'Test University',
            'code' => 'TST'
        ]);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'university_id' => $this->university->id,
            'is_active' => true,
        ]);
        $role = Role::where('name', 'research_admin')->first();
        $this->admin->roles()->attach($role);
    }

    /**
     * Test that index returns calls without sensitive fields.
     */
    public function test_index_hides_sensitive_fields(): void
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

        $response->assertOk();
        
        // Get the raw response content
        $content = $response->json();
        
        // Check if 'data' exists (it should for paginated resources)
        if (isset($content['data']) && is_array($content['data']) && count($content['data']) > 0) {
            $data = $content['data'][0];
        } else {
            // Fallback: response might be in different format
            $data = $content;
        }

        // Verify sensitive fields are NOT present
        $sensitiveFields = [
            'university_id',
            'campus_id',
            'faculty_id',
            'department_id',
            'research_center_id',
            'created_by',
            'is_featured',
            'metadata',
        ];

        foreach ($sensitiveFields as $field) {
            $this->assertArrayNotHasKey(
                $field,
                $data,
                "Sensitive field '{$field}' should not be exposed in API response"
            );
        }

        // Verify allowed fields ARE present
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('description', $data);
    }

    /**
     * Test that show returns call without sensitive fields.
     */
    public function test_show_hides_sensitive_fields(): void
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

        // Verify sensitive fields are NOT present
        $this->assertArrayNotHasKey('university_id', $data);
        $this->assertArrayNotHasKey('created_by', $data);
        $this->assertArrayNotHasKey('campus_id', $data);

        // Verify allowed fields ARE present
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
    }

    /**
     * Test that store returns created call without sensitive fields.
     */
    public function test_store_hides_sensitive_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/calls', [
            'title' => 'New Call',
            'description' => 'New Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'status_id' => $this->openStatus->id,
        ]);

        $response->assertCreated();
        $data = $response->json('data');

        // Verify sensitive fields are NOT present
        $this->assertArrayNotHasKey('university_id', $data);
        $this->assertArrayNotHasKey('created_by', $data);

        // Verify allowed fields ARE present
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
    }

    /**
     * Test that update returns updated call without sensitive fields.
     */
    public function test_update_hides_sensitive_fields(): void
    {
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $this->university->id,
            'status_id' => $this->openStatus->id,
            'created_by' => $this->admin->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/calls/{$call->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertOk();
        $data = $response->json('data');

        // Verify sensitive fields are NOT present
        $this->assertArrayNotHasKey('university_id', $data);
        $this->assertArrayNotHasKey('created_by', $data);

        // Verify allowed fields ARE present
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
    }
}
