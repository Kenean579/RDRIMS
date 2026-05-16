<?php

namespace App\Services;

use App\Models\DetectionRequest;
use App\Models\Proposal;
use App\Models\User;

class DetectionService
{
    public function submitRequest(Proposal $proposal, User $user): DetectionRequest
    {
        return $proposal->detectionRequest()->create([
            'request_date' => now(),
            'status' => 'pending',
            'submitted_by' => $user->id,
        ]);
    }

    public function complete(DetectionRequest $request, float $similarityScore, ?string $reportUrl = null): void
    {
        $request->update([
            'status' => 'completed',
            'similarity_score' => $similarityScore,
            'report_url' => $reportUrl,
            'completion_date' => now(),
        ]);
    }
}
