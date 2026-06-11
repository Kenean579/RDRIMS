<?php

namespace App\Jobs;

use App\Models\DetectionRequest;
use App\Models\File;
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

    public function handle(): void
    {
        // 1. Mark as processing
        $this->detectionRequest->update(['status_id' => 2]); // processing

        try {
            // 2. Get the file content
            $file = $this->detectionRequest->file;
            $text = $this->extractText($file);

            if (empty($text)) {
                $this->markFailed('Unable to extract text from file');
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
                $this->markFailed('Submission failed: ' . ($submitResponse['message'] ?? 'Unknown error'));
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
                    $this->markFailed('PlagiarismCheck.org reported a failure');
                    return;
                }
            }

            if (!$status) {
                $this->markFailed('Timed out waiting for results');
                return;
            }

            // 5. Get detailed report
            $reportResponse = Http::asForm()->post(
                config('services.plagiarismcheck.base_url') . "report/{$checkId}/",
                ['group_token' => \App\Models\Setting::where('key', 'plagiarismcheck_group_token')->value('value')]
            );

            $detailedReport = $reportResponse['data'] ?? null;

            // 6. Store results
            $this->detectionRequest->results()->create([
                'similarity_score' => $status['report']['percent'] ?? 0,
                'ai_probability'   => null, // PlagiarismCheck.org does not detect AI
                'raw_response'     => json_encode([
                    'status'          => $status,
                    'detailed_report' => $detailedReport,
                    'check_id'        => $checkId,
                ]),
            ]);

            $this->detectionRequest->update([
                'status_id'    => 3, // completed
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            $this->markFailed('Processing error: ' . $e->getMessage());
            throw $e;
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

    private function markFailed(string $reason): void
    {
        $this->detectionRequest->results()->create([
            'similarity_score' => 0,
            'ai_probability'   => null,
            'raw_response'     => ['error' => $reason],
        ]);

        $this->detectionRequest->update([
            'status_id' => 4, // failed
        ]);
    }
}