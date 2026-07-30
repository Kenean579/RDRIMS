<?php

namespace App\Jobs;

use App\Models\DetectionRequest;
use App\Services\DetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private DetectionRequest $detectionRequest,
    ) {}

    public function handle(DetectionService $service): void
    {
        // Mark as processing
        $service->markProcessing($this->detectionRequest);

        try {
            // For now: simple local similarity check (placeholder)
            // In production, call external API (Turnitin, Copyleaks, etc.)
            $similarityScore = random_int(0, 100) / 100; // dummy
            $aiProbability = random_int(0, 100) / 100;   // dummy

            // Complete request with results
            $service->completeRequest(
                $this->detectionRequest,
                $similarityScore,
                $aiProbability,
                json_encode(['message' => 'Local detection completed.'])
            );

        } catch (\Exception $e) {
            $service->markFailed($this->detectionRequest, $e->getMessage());
            \Illuminate\Support\Facades\Log::error("Detection background job failed for Request ID: {$this->detectionRequest->id}. Error: " . $e->getMessage());
        }
    }
}
