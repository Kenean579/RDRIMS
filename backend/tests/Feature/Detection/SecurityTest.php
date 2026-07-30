<?php

namespace Tests\Feature\Detection;

use App\Models\DetectionRequest;
use App\Models\DetectionStatus;
use App\Models\DetectionService;
use App\Models\File;
use App\Models\User;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private University $university1;
    private University $university2;
    private User $researcher1;
    private User $researcher2;
    private User $admin1;
    private File $file1;
    private File $file2;
    private DetectionService $service;
    private DetectionStatus $pendingStatus;
    private DetectionStatus $completedStatus;
    private DetectionStatus $failedStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup universities and users (simplified - no Campus/Faculty/Department)
        $this->university1 = University::factory()->create(['name' => 'University 1']);
        $this->university2 = University::factory()->create(['name' => 'University 2']);

        $this->researcher1 = User::factory()->create([
            'university_id' => $this->university1->id,
        ]);
        $this->researcher1->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'researcher'])->id
        );

        $this->researcher2 = User::factory()->create([
            'university_id' => $this->university2->id,
        ]);
        $this->researcher2->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'researcher'])->id
        );

        $this->admin1 = User::factory()->create([
            'university_id' => $this->university1->id,
        ]);
        $this->admin1->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        // Create files
        $this->file1 = File::factory()->create(['uploaded_by' => $this->researcher1->id]);
        $this->file2 = File::factory()->create(['uploaded_by' => $this->researcher2->id]);

        // Create detection service and status
        $this->service = DetectionService::firstOrCreate(['name' => 'turnitin']);
        $this->pendingStatus = DetectionStatus::firstOrCreate(['name' => 'pending']);
        DetectionStatus::firstOrCreate(['name' => 'processing']);
        $this->completedStatus = DetectionStatus::firstOrCreate(['name' => 'completed']);
        $this->failedStatus = DetectionStatus::firstOrCreate(['name' => 'failed']);
    }

    public function test_user_cannot_view_other_users_detection_request(): void
    {
        // Create request for researcher1
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
        ]);

        // Researcher2 tries to view researcher1's request
        Sanctum::actingAs($this->researcher2);
        $response = $this->getJson("/api/detection/requests/{$request->id}");

        $response->assertForbidden();
    }

    public function test_cross_tenant_user_cannot_view_detection_request(): void
    {
        // Create request for researcher1 (University 1)
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
        ]);

        // Researcher2 (University 2) tries to view
        Sanctum::actingAs($this->researcher2);
        $response = $this->getJson("/api/detection/requests/{$request->id}");

        $response->assertForbidden();
    }

    public function test_user_can_view_own_detection_request(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
        ]);

        Sanctum::actingAs($this->researcher1);
        $response = $this->getJson("/api/detection/requests/{$request->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $request->id);
    }

    public function test_admin_from_same_institution_can_view_detection_request(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
        ]);

        // Admin from same institution
        Sanctum::actingAs($this->admin1);
        $response = $this->getJson("/api/detection/requests/{$request->id}");

        $response->assertOk();
    }

    public function test_cannot_create_detection_request_for_unowned_file(): void
    {
        // Researcher1 tries to create request with researcher2's file
        Sanctum::actingAs($this->researcher1);
        $response = $this->postJson('/api/detection/requests', [
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file2->id, // Researcher2's file
        ]);

        // Should return 422 (Validation error) since file ownership is validated as a rule
        $response->assertStatus(422);
        $response->assertJsonPath('errors.file_id', ['You do not have permission to use this file.']);
    }

    public function test_list_only_shows_user_accessible_requests(): void
    {
        // Create requests for both users
        DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
        ]);

        DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 2,
            'file_id' => $this->file2->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher2->id,
            'requested_at' => now(),
        ]);

        // Researcher1 should only see their own request
        Sanctum::actingAs($this->researcher1);
        $response = $this->getJson('/api/detection/requests');

        $response->assertOk();
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
    }

    public function test_cannot_complete_other_users_detection_request(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
        ]);

        // Researcher (non-admin) tries to complete
        Sanctum::actingAs($this->researcher2);
        $response = $this->postJson("/api/detection/requests/{$request->id}/complete", [
            'similarity_score' => 15.5,
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_delete_completed_detection_request(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->completedStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
            'completed_at' => now(),
        ]);

        Sanctum::actingAs($this->researcher1);
        $response = $this->deleteJson("/api/detection/requests/{$request->id}");

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_mark_request_as_reviewed(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->completedStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
            'completed_at' => now(),
        ]);

        Sanctum::actingAs($this->researcher1);
        $response = $this->postJson("/api/detection/requests/{$request->id}/mark-reviewed");

        $response->assertForbidden();
    }

    public function test_admin_can_mark_request_as_reviewed(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->completedStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
            'completed_at' => now(),
        ]);

        Sanctum::actingAs($this->admin1);
        $response = $this->postJson("/api/detection/requests/{$request->id}/mark-reviewed");

        $response->assertOk();
        
        $request->refresh();
        $this->assertNotNull($request->reviewed_at);
        $this->assertEquals($this->admin1->id, $request->reviewed_by);
    }

    public function test_only_requester_can_retry_failed_request(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->failedStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
        ]);

        // Researcher2 tries to retry researcher1's request
        Sanctum::actingAs($this->researcher2);
        $response = $this->postJson("/api/detection/requests/{$request->id}/retry");

        $response->assertForbidden();
    }

    public function test_requester_can_retry_own_failed_request(): void
    {
        // Prevent jobs from running immediately
        \Illuminate\Support\Facades\Queue::fake();
        
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file1->id,
            'service_id' => $this->service->id,
            'status_id' => $this->failedStatus->id,
            'requested_by' => $this->researcher1->id,
            'requested_at' => now(),
        ]);

        Sanctum::actingAs($this->researcher1);
        $response = $this->postJson("/api/detection/requests/{$request->id}/retry");

        $response->assertStatus(202);
        
        $request->refresh();
        $this->assertEquals($this->pendingStatus->id, $request->status_id);
    }

    public function test_guest_cannot_access_detection_endpoints(): void
    {
        $response = $this->getJson('/api/detection/requests');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/detection/requests', []);
        $response->assertUnauthorized();
    }
}
