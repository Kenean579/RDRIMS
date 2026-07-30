<?php

namespace App\Services;

use App\Models\DetectionRequest;
use App\Models\DetectionStatus;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DetectionService
{
    /**
     * Create a new detection request with proper initialization
     */
    public function createRequest(array $data, User $user): DetectionRequest
    {
        return DB::transaction(function () use ($data, $user) {
            // Get pending status
            $pendingStatus = DetectionStatus::where('name', 'pending')->first();
            if (!$pendingStatus) {
                throw new \Exception('Pending status not found. Please seed detection statuses.');
            }

            // Set audit fields
            $data['requested_by'] = $user->id;
            $data['requested_at'] = now();
            $data['status_id'] = $pendingStatus->id;

            // Create request - guarded fields protected by model
            $request = DetectionRequest::create($data);

            // Log audit event
            Log::info("Detection request created", [
                'detection_request_id' => $request->id,
                'requested_by' => $user->id,
                'detectable_type' => $request->detectable_type,
            ]);

            return $request->fresh(['requestedBy', 'status', 'service', 'results']);
        });
    }

    /**
     * Mark detection request as completed with results
     */
    public function completeRequest(
        DetectionRequest $request,
        float $similarityScore,
        ?float $aiProbability = null,
        ?string $reportData = null,
        User $completedBy = null
    ): DetectionRequest {
        return DB::transaction(function () use ($request, $similarityScore, $aiProbability, $reportData, $completedBy) {
            if (!$completedBy) {
                $completedBy = auth()->user();
            }

            // Cannot complete already completed request
            if ($request->isCompleted()) {
                throw new \InvalidArgumentException('Request is already completed');
            }

            // Get completed status
            $completedStatus = DetectionStatus::where('name', 'completed')->first();

            // Update request with completion info
            $request->update([
                'status_id' => $completedStatus->id,
                'completed_at' => now(),
                'completed_by' => $completedBy->id,
            ]);

            // Create result record
            $request->results()->create([
                'similarity_score' => $similarityScore,
                'ai_probability' => $aiProbability,
                'raw_response' => $reportData ? json_decode($reportData, true) : null,
            ]);

            Log::info("Detection request completed", [
                'detection_request_id' => $request->id,
                'completed_by' => $completedBy->id,
                'similarity_score' => $similarityScore,
            ]);

            return $request->fresh(['results', 'completedBy', 'status']);
        });
    }

    /**
     * Mark detection request as reviewed
     */
    public function markReviewed(DetectionRequest $request, User $reviewedBy = null): DetectionRequest
    {
        return DB::transaction(function () use ($request, $reviewedBy) {
            if (!$reviewedBy) {
                $reviewedBy = auth()->user();
            }

            // Can only review completed requests
            if (!$request->isCompleted()) {
                throw new \InvalidArgumentException('Only completed requests can be reviewed');
            }

            $request->update([
                'reviewed_by' => $reviewedBy->id,
                'reviewed_at' => now(),
            ]);

            Log::info("Detection request marked as reviewed", [
                'detection_request_id' => $request->id,
                'reviewed_by' => $reviewedBy->id,
            ]);

            return $request->fresh(['reviewedBy']);
        });
    }

    /**
     * Retry a failed detection request
     */
    public function retryRequest(DetectionRequest $request, User $user): DetectionRequest
    {
        // Only the requester or admin can retry
        if ($request->requested_by !== $user->id && !$user->hasRole(['super_admin', 'admin'])) {
            throw new \InvalidArgumentException('Only the requester or admin can retry');
        }

        // Can only retry failed or pending requests
        if (!$request->isFailed() && !$request->isPending()) {
            throw new \InvalidArgumentException('Cannot retry completed or reviewed requests');
        }

        return DB::transaction(function () use ($request) {
            // Get pending status
            $pendingStatus = DetectionStatus::where('name', 'pending')->first();

            // Delete old results first
            $request->results()->forceDelete();

            // Update request to reset to pending state
            $request->update([
                'status_id' => $pendingStatus->id,
                'completed_at' => null,
                'completed_by' => null,
                'reviewed_at' => null,
                'reviewed_by' => null,
            ]);

            Log::info("Detection request retried", [
                'detection_request_id' => $request->id,
                'retried_by' => $request->requested_by,
            ]);

            // Reload and return
            return $request->fresh(['status', 'requestedBy', 'service']);
        });
    }

    /**
     * Mark request as processing (called when job starts)
     */
    public function markProcessing(DetectionRequest $request): DetectionRequest
    {
        return DB::transaction(function () use ($request) {
            $processingStatus = DetectionStatus::where('name', 'processing')->first();

            $request->update([
                'status_id' => $processingStatus->id,
            ]);

            return $request->fresh(['status']);
        });
    }

    /**
     * Mark request as failed (called when job fails)
     */
    public function markFailed(DetectionRequest $request, string $errorMessage = null): DetectionRequest
    {
        return DB::transaction(function () use ($request, $errorMessage) {
            $failedStatus = DetectionStatus::where('name', 'failed')->first();

            $request->update([
                'status_id' => $failedStatus->id,
            ]);

            // Create error result
            $request->results()->create([
                'similarity_score' => 0,
                'ai_probability' => null,
                'raw_response' => ['error' => $errorMessage ?? 'Processing failed'],
            ]);

            Log::error("Detection request failed", [
                'detection_request_id' => $request->id,
                'error' => $errorMessage,
            ]);

            return $request->fresh(['results', 'status']);
        });
    }

    /**
     * Submit request for processing (calls the appropriate job)
     * Preserved for backward compatibility
     */
    public function submitRequest(Proposal $proposal, User $user): DetectionRequest
    {
        return $this->createRequest([
            'detectable_type' => 'Proposal',
            'detectable_id' => $proposal->id,
            'file_id' => $proposal->file_id,
        ], $user);
    }

    /**
     * Complete request (preserved for backward compatibility)
     */
    public function complete(DetectionRequest $request, float $similarityScore, ?string $reportUrl = null): void
    {
        $this->completeRequest($request, $similarityScore);
    }
}
