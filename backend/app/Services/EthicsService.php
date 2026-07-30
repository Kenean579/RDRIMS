<?php

namespace App\Services;

use App\Models\EthicsRequest;
use App\Models\Proposal;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EthicsService
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Generate an Ethics/IRB request PDF from proposal data and store it.
     */
    public function generateRequest(Proposal $proposal, User $user): EthicsRequest
    {
        return DB::transaction(function () use ($proposal, $user) {
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

            // Get pending status
            $pendingStatusId = EthicsRequest::getStatusId('pending');

            return EthicsRequest::create([
                'proposal_id' => $proposal->id,
                'generated_pdf_path' => $filename,
                'submitted_to_irb' => false,
                'approval_status_id' => $pendingStatusId,
                'version' => $lastVersion + 1,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        });
    }

    /**
     * Mark ethics request as submitted to IRB
     */
    public function markAsSubmitted(EthicsRequest $request, User $user): EthicsRequest
    {
        return DB::transaction(function () use ($request, $user) {
            $request->update([
                'submitted_to_irb' => true,
                'updated_by' => $user->id,
            ]);

            return $request->fresh();
        });
    }

    /**
     * Approve ethics request
     */
    public function approve(EthicsRequest $request, User $approvedBy, ?string $comment = null): EthicsRequest
    {
        return $this->makeDecision($request, 'approved', $approvedBy, $comment);
    }

    /**
     * Reject ethics request
     */
    public function reject(EthicsRequest $request, User $rejectedBy, string $comment): EthicsRequest
    {
        return $this->makeDecision($request, 'rejected', $rejectedBy, $comment);
    }

    /**
     * Request revision on ethics request
     */
    public function requestRevision(EthicsRequest $request, User $reviewer, string $comment): EthicsRequest
    {
        return $this->makeDecision($request, 'needs_revision', $reviewer, $comment);
    }

    /**
     * Make a decision on ethics request (approve/reject/needs_revision)
     */
    public function makeDecision(EthicsRequest $request, string $status, User $reviewer, ?string $comment = null): EthicsRequest
    {
        return DB::transaction(function () use ($request, $status, $reviewer, $comment) {
            // Validate status
            $validStatuses = ['approved', 'rejected', 'needs_revision'];
            if (!in_array($status, $validStatuses)) {
                throw new \InvalidArgumentException("Invalid status: {$status}");
            }

            // Get status ID
            $statusId = EthicsRequest::getStatusId($status);

            // Update request with decision
            $request->update([
                'approval_status_id' => $statusId,
                'comments' => $comment,
                'reviewer_id' => $reviewer->id,
                'reviewed_at' => now(),
                'updated_by' => $reviewer->id,
            ]);

            // Send notification to submitter
            if ($request->proposal && $request->proposal->submittedBy) {
                $this->notificationService->ethicsDecisionMade(
                    $request->proposal->submittedBy,
                    $request->proposal->title,
                    $status,
                    $comment
                );
            }

            return $request->fresh();
        });
    }

    /**
     * Update ethics request (resubmit after revision request)
     */
    public function updateRequest(EthicsRequest $request, array $data, User $user): EthicsRequest
    {
        return DB::transaction(function () use ($request, $data, $user) {
            // Can only update if editable
            if (!$request->canEdit()) {
                throw new \InvalidArgumentException('Ethics request cannot be edited in current status');
            }

            // Remove sensitive fields
            unset($data['approval_status_id'], $data['reviewer_id'], $data['reviewed_at']);

            $data['updated_by'] = $user->id;

            $request->update($data);

            return $request->fresh();
        });
    }
}
