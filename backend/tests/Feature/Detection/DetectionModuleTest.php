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

class DetectionModuleTest extends TestCase
{
    use RefreshDatabase;

    private University $university;
    private User $researcher;
    private User $admin;
    private File $file;
    private DetectionService $service;
    private DetectionStatus $pendingStatus;
    private DetectionStatus $completedStatus;
    private DetectionStatus $failedStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Simplified setup matching Output module pattern
        $this->university = University::factory()->create();

        $this->researcher = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $this->researcher->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'researcher'])->id
        );

        $this->admin = User::factory()->create([
            'university_id' => $this->university->id,
        ]);
        $this->admin->roles()->attach(
            \App\Models\Role::firstOrCreate(['name' => 'admin'])->id
        );

        $this->file = File::factory()->create(['uploaded_by' => $this->researcher->id]);

        $this->service = DetectionService::firstOrCreate(['name' => 'turnitin']);
        $this->pendingStatus = DetectionStatus::firstOrCreate(['name' => 'pending']);
        DetectionStatus::firstOrCreate(['name' => 'processing']);
        $this->completedStatus = DetectionStatus::firstOrCreate(['name' => 'completed']);
        $this->failedStatus = DetectionStatus::firstOrCreate(['name' => 'failed']);
    }

    public function test_can_create_detection_request_with_valid_data(): void
    {
        Sanctum::actingAs($this->researcher);
        $response = $this->postJson('/api/detection/requests', [
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
        ]);

        $response->assertStatus(202);
        $response->assertJsonStructure([
            'message',
            'request' => ['id', 'status', 'requested_by', 'is_pending'],
        ]);

        $this->assertDatabaseHas('detection_requests', [
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'requested_by' => $this->researcher->id,
        ]);
    }

    public function test_can_read_own_detection_request(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        Sanctum::actingAs($this->researcher);
        $response = $this->getJson("/api/detection/requests/{$request->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $request->id);
        $response->assertJsonPath('requested_by.id', $this->researcher->id);
    }

    public function test_can_list_own_detection_requests_paginated(): void
    {
        DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        Sanctum::actingAs($this->researcher);
        $response = $this->getJson('/api/detection/requests');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'status', 'requested_by', 'is_pending'],
            ],
            'meta',
        ]);
    }

    public function test_can_get_detection_services_list(): void
    {
        Sanctum::actingAs($this->researcher);
        $response = $this->getJson('/api/detection/services');

        $response->assertOk();
        $response->assertJsonStructure(['*' => ['id', 'name']]);
    }

    public function test_cannot_create_request_with_invalid_file_id(): void
    {
        Sanctum::actingAs($this->researcher);
        $response = $this->postJson('/api/detection/requests', [
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => 99999, // Non-existent
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_request_with_missing_detectable_type(): void
    {
        Sanctum::actingAs($this->researcher);
        $response = $this->postJson('/api/detection/requests', [
            'detectable_id' => 1,
            'file_id' => $this->file->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_status_starts_as_pending(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        $this->assertTrue($request->isPending());
        $this->assertFalse($request->isCompleted());
    }

    public function test_can_transition_pending_to_processing(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        $processingStatus = DetectionStatus::where('name', 'processing')->first();
        $request->update(['status_id' => $processingStatus->id]);

        $this->assertTrue($request->isProcessing());
    }

    public function test_can_transition_processing_to_completed(): void
    {
        $processingStatus = DetectionStatus::where('name', 'processing')->first();
        
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $processingStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        $request->update([
            'status_id' => $this->completedStatus->id,
            'completed_at' => now(),
            'completed_by' => $this->admin->id,
        ]);

        $this->assertTrue($request->isCompleted());
        $this->assertNotNull($request->completed_at);
    }

    public function test_can_mark_completed_request_as_reviewed(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->completedStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
            'completed_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/detection/requests/{$request->id}/mark-reviewed");

        $response->assertOk();
        
        $request->refresh();
        $this->assertTrue($request->isReviewed());
        $this->assertNotNull($request->reviewed_at);
        $this->assertEquals($this->admin->id, $request->reviewed_by);
    }

    public function test_can_retry_failed_detection(): void
    {
        // Prevent jobs from running immediately
        \Illuminate\Support\Facades\Queue::fake();
        
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->failedStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        Sanctum::actingAs($this->researcher);
        $response = $this->postJson("/api/detection/requests/{$request->id}/retry");

        $response->assertStatus(202);
        
        // Verify the job was dispatched
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessDetectionJob::class);
        
        // Check database - should be pending after retry
        $this->assertDatabaseHas('detection_requests', [
            'id' => $request->id,
            'status_id' => $this->pendingStatus->id,
        ]);
        
        $request->refresh();
        $this->assertEquals($this->pendingStatus->id, $request->status_id);
        $this->assertTrue($request->isPending());
    }

    public function test_can_delete_pending_request(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        Sanctum::actingAs($this->researcher);
        $response = $this->deleteJson("/api/detection/requests/{$request->id}");

        $response->assertOk();
        $this->assertSoftDeleted('detection_requests', ['id' => $request->id]);
    }

    public function test_requested_by_field_is_set_automatically(): void
    {
        Sanctum::actingAs($this->researcher);
        $response = $this->postJson('/api/detection/requests', [
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
        ]);

        $response->assertStatus(202);
        
        $requestId = $response->json('request.id');
        $request = DetectionRequest::find($requestId);
        
        $this->assertEquals($this->researcher->id, $request->requested_by);
    }

    public function test_completed_by_field_is_set_when_completed(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/detection/requests/{$request->id}/complete", [
            'similarity_score' => 25.5,
            'ai_probability' => 10.3,
        ]);

        $response->assertOk();
        
        $request->refresh();
        $this->assertEquals($this->admin->id, $request->completed_by);
        $this->assertNotNull($request->completed_at);
    }

    public function test_reviewed_by_field_is_set_when_reviewed(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->completedStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
            'completed_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/detection/requests/{$request->id}/mark-reviewed");

        $response->assertOk();
        
        $request->refresh();
        $this->assertEquals($this->admin->id, $request->reviewed_by);
        $this->assertNotNull($request->reviewed_at);
    }

    public function test_can_restore_soft_deleted_request(): void
    {
        $request = DetectionRequest::create([
            'detectable_type' => 'Proposal',
            'detectable_id' => 1,
            'file_id' => $this->file->id,
            'service_id' => $this->service->id,
            'status_id' => $this->pendingStatus->id,
            'requested_by' => $this->researcher->id,
            'requested_at' => now(),
        ]);

        $request->delete();

        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/detection/requests/{$request->id}/restore");

        $response->assertOk();
        $this->assertDatabaseHas('detection_requests', [
            'id' => $request->id,
            'deleted_at' => null,
        ]);
    }
}
