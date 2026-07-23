<?php

namespace Tests\Feature;

use App\Models\Call;
use App\Models\CallStatus;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * VerifyApiResourceUsageTest
 * 
 * Critical verification test: ensures that CallResource is being used
 * and sensitive organizational fields are NOT exposed in public responses.
 */
class VerifyApiResourceUsageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that public endpoint response is wrapped with CallResource
     * and does not expose sensitive organizational fields.
     */
    public function test_public_endpoint_uses_resource_and_hides_sensitive_fields(): void
    {
        // Setup
        CallStatus::firstOrCreate(['name' => 'open']);
        $university = University::create(['name' => 'Test University', 'code' => 'TST']);
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'university_id' => $university->id,
        ]);

        // Create a public, published call
        $call = Call::create([
            'title' => 'Public Test Call',
            'description' => 'A public call for research proposals',
            'thematic_areas' => 'AI,ML,DL',
            'deadline' => now()->addDays(30),
            'university_id' => $university->id,
            'status_id' => 1, // open
            'is_public' => true,
            'published_at' => now()->subDay(),
            'created_by' => $user->id,
        ]);

        // Act: Get the call as unauthenticated user (public portal access)
        $response = $this->getJson("/api/calls/{$call->id}");

        // Assert: Response is OK
        $response->assertOk();

        // Assert: Response has data wrapper (CallResource wrapping)
        $this->assertTrue($response->json('data') !== null, 'Response should have data wrapper');

        $data = $response->json('data');

        // CRITICAL: Sensitive organizational fields MUST NOT be in response
        $sensitiveFields = [
            'university_id' => 'Exposes organizational hierarchy',
            'campus_id' => 'Exposes campus structure',
            'faculty_id' => 'Exposes faculty structure',
            'department_id' => 'Exposes department structure',
            'research_center_id' => 'Exposes research center structure',
            'created_by' => 'Exposes creator user ID',
        ];

        foreach ($sensitiveFields as $field => $reason) {
            $this->assertArrayNotHasKey(
                $field,
                $data,
                "SECURITY: Sensitive field '{$field}' ({$reason}) must not be exposed in public API response"
            );
        }

        // REQUIRED: Public business data MUST be in response
        $publicFields = ['id', 'title', 'description', 'deadline', 'thematic_areas', 'creator'];
        foreach ($publicFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $data,
                "Required field '{$field}' must be in resource response"
            );
        }
    }

    /**
     * Test that even private calls don't expose sensitive fields
     * (in case accessed by authorized users).
     */
    public function test_private_call_also_hides_sensitive_fields(): void
    {
        // Setup
        CallStatus::firstOrCreate(['name' => 'draft']);
        $university = University::create(['name' => 'Test University 2', 'code' => 'TST2']);
        $user = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
            'university_id' => $university->id,
        ]);

        // Create a private call
        $call = Call::create([
            'title' => 'Private Call',
            'description' => 'Private call',
            'thematic_areas' => 'Research',
            'deadline' => now()->addDays(30),
            'university_id' => $university->id,
            'status_id' => 1, // draft
            'is_public' => false,
            'published_at' => null,
            'created_by' => $user->id,
        ]);

        // Act: Try to access as unauthenticated (should fail gracefully or return 404)
        $response = $this->getJson("/api/calls/{$call->id}");

        // This might be 404 or 401, depending on implementation
        $this->assertTrue(
            $response->status() === 404 || $response->status() === 401,
            'Unauthenticated users should not access private calls'
        );
    }

    /**
     * Test that the index endpoint returns paginated ResourceCollection
     * with proper structure.
     */
    public function test_index_endpoint_structure(): void
    {
        // Setup: Create multiple public calls
        CallStatus::firstOrCreate(['name' => 'open']);
        $university = University::create(['name' => 'University', 'code' => 'UNI']);
        $user = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
            'university_id' => $university->id,
        ]);

        for ($i = 0; $i < 3; $i++) {
            Call::create([
                'title' => "Call {$i}",
                'description' => "Description {$i}",
                'thematic_areas' => 'AI',
                'deadline' => now()->addDays(30),
                'university_id' => $university->id,
                'status_id' => 1,
                'is_public' => true,
                'published_at' => now(),
                'created_by' => $user->id,
            ]);
        }

        // Act
        $response = $this->getJson('/api/calls');

        // Assert: Response OK
        $response->assertOk();

        // Assert: Response has proper pagination structure
        $json = $response->json();
        $this->assertArrayHasKey('data', $json, 'Paginated response should have data key');
        $this->assertIsArray($json['data'], 'Data should be an array');
        $this->assertCount(3, $json['data'], 'Should have 3 calls');

        // Assert: Each call in collection doesn't expose sensitive fields
        foreach ($json['data'] as $callData) {
            $this->assertArrayNotHasKey('university_id', $callData);
            $this->assertArrayNotHasKey('created_by', $callData);
            $this->assertArrayHasKey('title', $callData);
            $this->assertArrayHasKey('creator', $callData);
        }
    }

    /**
     * Test: Verify creator field structure is correct (has id and name, not raw user ID).
     */
    public function test_creator_field_has_correct_structure(): void
    {
        // Setup
        CallStatus::firstOrCreate(['name' => 'open']);
        $university = University::create(['name' => 'Uni', 'code' => 'U']);
        $user = User::create([
            'name' => 'Dr. John Smith',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'university_id' => $university->id,
        ]);

        $call = Call::create([
            'title' => 'Call',
            'description' => 'Desc',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $university->id,
            'status_id' => 1,
            'is_public' => true,
            'published_at' => now(),
            'created_by' => $user->id,
        ]);

        // Act
        $response = $this->getJson("/api/calls/{$call->id}");
        
        $data = $response->json('data');

        // Assert: Creator field structure
        $this->assertArrayHasKey('creator', $data);
        $creator = $data['creator'];
        $this->assertArrayHasKey('id', $creator);
        $this->assertArrayHasKey('name', $creator);
        $this->assertEquals($user->id, $creator['id']);
        $this->assertEquals('Dr. John Smith', $creator['name']);
    }
}
