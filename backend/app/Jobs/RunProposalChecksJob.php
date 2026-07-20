<?php

namespace App\Jobs;

use App\Models\Proposal;
use App\Models\ProposalStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunProposalChecksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Proposal $proposal)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Running plagiarism and automated background checks for Proposal ID: {$this->proposal->id}");
        
        // Mocking an external API call delay
        sleep(2);

        // Generate synthetic score: 85 - 100
        $score = mt_rand(85, 99) + (mt_rand(0, 99) / 100);

        // Update the database with the API results
        $this->proposal->update([
            'originality_score' => $score,
            'plagiarism_report_url' => "https://plagiarism.mockapi.service/report/{$this->proposal->id}",
            'status_id' => ProposalStatus::where('name', 'submitted')->first()->id ?? 2
        ]);

        Log::info("Checks completed for Proposal ID: {$this->proposal->id} with score: {$score}%");
    }
}
