<?php

namespace App\Jobs;

use App\Models\DetectionRequest;
use App\Models\File;
use App\Services\DetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProcessPlagiarismCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private DetectionRequest $detectionRequest,
    ) {}

    public function handle(DetectionService $service): void
    {
        // 1. Mark as processing
        $service->markProcessing($this->detectionRequest);

        try {
            // 2. Get the file content
            $file = $this->detectionRequest->file;
            $text = $this->extractText($file);

            if (empty($text)) {
                $service->markFailed($this->detectionRequest, 'Unable to extract text from file');
                return;
            }

            // 3. Submit to PlagiarismCheck.org
            $submitResponse = Http::asForm()->post(
                config('services.plagiarismcheck.base_url') . 'check/',
                [
                    'group_token' => \App\Models\Setting::where('key', 'plagiarismcheck_group_token')->value('value'),
                    'author'      => \App\Models\Setting::where('key', 'plagiarismcheck_author_email')->value('value'),
                    'text'        => $text,
                ]
            );

            if (!$submitResponse->successful() || !$submitResponse['success']) {
                $service->markFailed($this->detectionRequest, 'Submission failed: ' . ($submitResponse['message'] ?? 'Unknown error'));
                return;
            }

            $checkId = $submitResponse['data']['id'];

            // 4. Poll for status
            $maxAttempts = 30; // ~5 minutes
            $attempt = 0;
            $status = null;

            while ($attempt < $maxAttempts) {
                sleep(10);
                $attempt++;

                $statusResponse = Http::asForm()->post(
                    config('services.plagiarismcheck.base_url') . "status/{$checkId}/",
                    ['group_token' => \App\Models\Setting::where('key', 'plagiarismcheck_group_token')->value('value')]
                );

                if (!$statusResponse->successful()) continue;

                $state = $statusResponse['data']['state'] ?? null;

                if ($state === 5) { // STATE_CHECKED
                    $reportData = $statusResponse['data'];
                    $status = $reportData;
                    break;
                }

                if ($state === 4) { // STATE_FAILED
                    $service->markFailed($this->detectionRequest, 'PlagiarismCheck.org reported a failure');
                    return;
                }
            }

            if (!$status) {
                $service->markFailed($this->detectionRequest, 'Timed out waiting for results');
                return;
            }

            // 5. Get detailed report
            $reportResponse = Http::asForm()->post(
                config('services.plagiarismcheck.base_url') . "report/{$checkId}/",
                ['group_token' => \App\Models\Setting::where('key', 'plagiarismcheck_group_token')->value('value')]
            );

            $detailedReport = $reportResponse['data'] ?? null;

            // 6. Complete request with results using service
            $service->completeRequest(
                $this->detectionRequest,
                $status['report']['percent'] ?? 0,
                null, // PlagiarismCheck.org does not detect AI
                json_encode([
                    'status'          => $status,
                    'detailed_report' => $detailedReport,
                    'check_id'        => $checkId,
                ])
            );

        } catch (\Exception $e) {
            $service->markFailed($this->detectionRequest, 'Processing error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error("Plagiarism check background job failed for Request ID: {$this->detectionRequest->id}. Error: " . $e->getMessage());
        }
    }

    private function extractText(File $file): string
    {
        try {
            $filePath = $file->file_path;
            
            if (!Storage::exists($filePath)) {
                return '';
            }

            $content = Storage::get($filePath);
            
            // If it's a PDF, we would need to extract text using a PDF library
            // For now, assuming plain text or simple extraction
            if (str_ends_with($filePath, '.pdf')) {
                // Basic text extraction - in production, use a proper PDF library
                // like TCPDF, FPDI, or PDFParser
                return $this->extractPdfText($content);
            }

            // For text files
            return $content;
        } catch (\Exception $e) {
            return '';
        }
    }

    private function extractPdfText(string $pdfContent): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            
            // To parse from byte content instead of reading from file:
            // Write to a temporary file because some versions of Smalot might prefer
            // parsing file paths. However, parseContent is available.
            $pdf = $parser->parseContent($pdfContent);
            return $pdf->getText();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF extraction failed: ' . $e->getMessage());
            return '';
        }
    }
}