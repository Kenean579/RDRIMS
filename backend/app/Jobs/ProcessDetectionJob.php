<?php
// app/Jobs/ProcessDetectionJob.php

namespace App\Jobs;

use App\Models\DetectionRequest;
use App\Models\DetectionStatus;
use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected DetectionRequest $detection;

    public function __construct(DetectionRequest $detection)
    {
        $this->detection = $detection;
    }

    public function handle()
    {
        // Mark as processing
        $processingId = DetectionStatus::where('name', 'processing')->value('id');
        $this->detection->update(['status_id' => $processingId]);

        try {
            $file = File::find($this->detection->file_id);
            $fileContent = Storage::disk('public')->get($file->file_path);
            $text = $this->extractText($fileContent, $file->file_path);

            // ================================================================
            // TODO: Replace with actual API call (Turnitin / Copyleaks / GPTZero)
            // Example:
            // $response = Http::post('https://api.detection.com/check', [
            //     'text' => $text,
            //     'service' => $this->detection->service->name,
            // ]);
            // $data = $response->json();
            // ================================================================

            // Mock results for demonstration
            $similarityScore = rand(0, 100) / 100;
            $aiProbability = rand(0, 100) / 100;

            $completedId = DetectionStatus::where('name', 'completed')->value('id');
            $this->detection->update([
                'status_id'    => $completedId,
                'completed_at' => now(),
            ]);

            $this->detection->result()->create([
                'similarity_score' => $similarityScore,
                'ai_probability'   => $aiProbability,
                'raw_response'     => json_encode(['mock' => true]),
            ]);

        } catch (\Exception $e) {
            $failedId = DetectionStatus::where('name', 'failed')->value('id');
            $this->detection->update([
                'status_id'    => $failedId,
                'completed_at' => now(),
            ]);
            Log::error('Detection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract plain text from PDF/DOCX or return as is for txt.
     */
    private function extractText(string $content, string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === 'pdf') {
            // Use a PDF parser library (e.g., Spatie/PdfToText)
            // For simplicity, return a placeholder.
            return 'Extracted text from PDF would be here.';
        }
        if ($extension === 'docx') {
            // Use a DOCX parser
            return 'Extracted text from DOCX would be here.';
        }
        return $content; // plain text
    }
}
