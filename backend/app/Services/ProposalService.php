<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ProposalService
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function submit(Proposal $proposal, User $user): void
    {
        if ($proposal->status->name !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Only draft proposals can be submitted.',
            ]);
        }

        if (empty($proposal->investigators) || $proposal->investigators->count() === 0) {
            throw ValidationException::withMessages([
                'investigators' => 'At least one investigator is required.',
            ]);
        }

        $proposal->update([
            'status_id' => Proposal::getStatusId('submitted'),
            'submitted_at' => now(),
            'submitted_by' => $user->id,
        ]);

        $this->auditLogService->log('submitted', 'proposals', $proposal->id, request());
    }

    public function approve(Proposal $proposal, User $approvedBy): void
    {
        if ($proposal->status->name !== 'under_review') {
            throw ValidationException::withMessages([
                'status' => 'Only proposals under review can be approved.',
            ]);
        }

        $proposal->update([
            'status_id' => Proposal::getStatusId('approved'),
            'approved_by' => $approvedBy->id,
            'approved_at' => now(),
        ]);

        // Automatically create a project from approved proposal
        $proposal->project()->create([
            'title' => $proposal->title,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'total_budget' => $proposal->budget,
            'status_id' => \App\Models\Project::getStatusId('active'),
            'pi_id' => $proposal->submitted_by,
            'academic_year_id' => $proposal->academic_year_id,
        ]);

        $this->auditLogService->log('approved', 'proposals', $proposal->id, request());
    }

    public function reject(Proposal $proposal, User $rejectedBy, string $comment): void
    {
        if (! in_array($proposal->status->name, ['submitted', 'under_review'])) {
            throw ValidationException::withMessages([
                'status' => 'Only submitted or under-review proposals can be rejected.',
            ]);
        }

        $proposal->update([
            'status_id' => Proposal::getStatusId('rejected'),
            'status_change_comment' => $comment,
        ]);

        $this->auditLogService->log('rejected', 'proposals', $proposal->id, request());
    }

    public function assignReviewers(Proposal $proposal, array $reviewerIds, User $assignedBy): void
    {
        foreach ($reviewerIds as $reviewerId) {
            $proposal->reviewers()->attach($reviewerId, [
                'assigned_by' => $assignedBy->id,
                'assigned_at' => now(),
            ]);
        }

        $proposal->update(['status_id' => Proposal::getStatusId('under_review')]);

        $this->auditLogService->log('reviewers_assigned', 'proposals', $proposal->id, request());
    }
}
