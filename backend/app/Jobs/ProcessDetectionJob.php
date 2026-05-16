<?php

namespace App\Jobs;

use App\Models\DetectionRequest;
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

    public function handle(): void
    {
        // Mark as processing
        $this->detectionRequest->update(['status_id' => 2]); // processing

        try {
            // For now: simple local similarity check (placeholder)
            // In production, call external API (Turnitin, Copyleaks, etc.)
            $similarityScore = random_int(0, 100) / 100; // dummy
            $aiProbability = random_int(0, 100) / 100;   // dummy

            $this->detectionRequest->results()->create([
                'similarity_score' => $similarityScore,
                'ai_probability' => $aiProbability,
                'raw_response' => ['message' => 'Local detection completed.'],
            ]);

            $this->detectionRequest->update([
                'status_id' => 3, // completed
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->detectionRequest->update(['status_id' => 4]); // failed
            throw $e;
        }
    }
}
