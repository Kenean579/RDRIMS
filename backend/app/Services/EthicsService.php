<?php

namespace App\Services;

use App\Models\EthicsRequest;
use App\Models\Proposal;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class EthicsService
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Generate an Ethics/IRB request PDF from proposal data and store it.
     */
    public function generateRequest(Proposal $proposal, User $user): EthicsRequest
    {
        $proposal->load('submittedBy.department');
        
        $data = [
            'title' => $proposal->title,
            'abstract' => $proposal->abstract,
            'objectives' => $proposal->objectives,
            'methodology' => $proposal->methodology,
            'submitted_by' => $proposal->submittedBy->name,
            'department' => $proposal->submittedBy->department->name ?? 'N/A',
            'date' => now()->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('pdfs.ethics_request', $data);
        
        $filename = 'ethics/proposal_' . $proposal->id . '_v' . (time()) . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        // Find existing request to increment version
        $lastVersion = EthicsRequest::where('proposal_id', $proposal->id)->max('version') ?? 0;

        return EthicsRequest::create([
            'proposal_id' => $proposal->id,
            'generated_pdf_path' => $filename,
            'submitted_to_irb' => false,
            'approval_status_id' => EthicsRequest::getStatusId('pending'),
            'version' => $lastVersion + 1,
        ]);
    }

    public function markAsSubmitted(EthicsRequest $request): void
    {
        $request->update(['submitted_to_irb' => true]);
    }

    public function approve(EthicsRequest $request, User $approvedBy, ?string $comment = null): void
    {
        $this->makeDecision($request, 'approved', $approvedBy, $comment);
    }

    public function reject(EthicsRequest $request, User $rejectedBy, string $comment): void
    {
        $this->makeDecision($request, 'rejected', $rejectedBy, $comment);
    }

    public function makeDecision(EthicsRequest $request, string $status, User $reviewer, ?string $comment = null): void
    {
        $request->update([
            'approval_status_id' => EthicsRequest::getStatusId($status),
            'comments' => $comment,
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        if ($request->proposal && $request->proposal->submittedBy) {
            $this->notificationService->ethicsDecisionMade(
                $request->proposal->submittedBy,
                $request->proposal->title,
                $status,
                $comment
            );
        }
    }
}
