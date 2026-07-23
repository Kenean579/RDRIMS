<?php

namespace Tests\Feature;

use App\Models\Call;
use App\Models\CallStatus;
use App\Models\University;
use App\Models\User;
use Tests\TestCase;

class CallTest extends TestCase
{
    /**
     * Test that CallResource hides sensitive organizational fields in API responses.
     */
    public function test_resource_does_not_expose_sensitive_fields(): void
    {
        // Setup
        CallStatus::firstOrCreate(['name' => 'open']);
        $university = University::create(['name' => 'Test University', 'code' => 'TST-UNI']);
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'university_id' => $university->id,
        ]);
        
        $call = Call::create([
            'title' => 'Test Call',
            'description' => 'Test Description',
            'thematic_areas' => 'AI',
            'deadline' => now()->addDays(30),
            'university_id' => $university->id,
            'is_public' => true,
            'published_at' => now()->subDay(),
            'status_id' => 1,
            'created_by' => $user->id,
        ]);

        // Act
        $response = $this->getJson('/api/calls');

        // Assert - first debug the response structure
        $response->assertOk();
        $json = $response->json();
        
        // Debug: Check what keys are in the response
        $this->assertIsArray($json, 'Response should be array');
        
        // Get first item - could be in data or directly
        if (isset($json['data']) && is_array($json['data'])) {
            $data = $json['data'][0] ?? null;
        } else if (is_array($json) && count($json) > 0) {
            $data = $json[0];
        } else {
            $data = null;
        }
        
        $this->assertIsArray($data, 'Should have call data');
        
        // Sensitive fields MUST NOT be exposed
        $this->assertArrayNotHasKey('university_id', $data, 'Sensitive field university_id should not be exposed');
        $this->assertArrayNotHasKey('campus_id', $data, 'Sensitive field campus_id should not be exposed');
        $this->assertArrayNotHasKey('faculty_id', $data, 'Sensitive field faculty_id should not be exposed');
        $this->assertArrayNotHasKey('department_id', $data, 'Sensitive field department_id should not be exposed');
        $this->assertArrayNotHasKey('created_by', $data, 'Sensitive field created_by should not be exposed');
        
        // Public business data MUST be exposed
        $this->assertArrayHasKey('title', $data, 'Public field title should be exposed');
        $this->assertArrayHasKey('deadline', $data, 'Public field deadline should be exposed');
    }
}
