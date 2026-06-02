<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\User;
use App\Models\ProposalStatus;
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

        // Checklist: Investigators
        if ($proposal->investigators()->count() === 0) {
            throw ValidationException::withMessages([
                'investigators' => 'At least one investigator is required.',
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatus::where('name', 'submitted')->first()->id,
            'submitted_at' => now(),
        ]);

        $this->auditLogService->log('submitted', 'proposals', $proposal->id, request());
    }

    public function approve(Proposal $proposal, User $approvedBy): void
    {
        // Checklist 1: Reviews
        $reviewCount = $proposal->reviewers()->count();
        $submittedReviews = $proposal->reviewers()->whereNotNull('submitted_at')->count();
        
        if ($reviewCount > 0 && $submittedReviews < $reviewCount) {
             throw ValidationException::withMessages([
                'reviews' => "Waiting for $reviewCount reviews. only $submittedReviews submitted.",
            ]);
        }

        // Checklist 2: Finance
        $pendingFinance = $proposal->financeChecks()->whereHas('status', fn($s) => $s->where('name', 'pending'))->exists();
        if ($pendingFinance) {
             throw ValidationException::withMessages([
                'finance' => "Pending finance check must be cleared before approval.",
            ]);
        }

        // Checklist 3: Ethics
        $pendingEthics = $proposal->ethicsRequests()->whereHas('approvalStatus', fn($s) => $s->where('name', 'pending'))->exists();
        if ($pendingEthics) {
             throw ValidationException::withMessages([
                'ethics' => "Pending ethics clearance must be approved before final proposal approval.",
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatus::where('name', 'approved')->first()->id,
            'approved_by' => $approvedBy->id,
            'approved_at' => now(),
        ]);

        // Automatically create a project from approved proposal
        $proposal->project()->create([
            'title' => $proposal->title,
            'start_date' => now(),
            'end_date' => now()->addMonths(config('app.default_project_duration', 12)),
            'total_budget' => $proposal->budget,
            'status_id' => \App\Models\Project::getStatusId('active'),
            'pi_id' => $proposal->submitted_by,
            'academic_year_id' => $proposal->academic_year_id,
        ]);

        $this->auditLogService->log('approved', 'proposals', $proposal->id, request());
    }

    public function reject(Proposal $proposal, User $rejectedBy, string $comment): void
    {
        $proposal->update([
            'status_id' => ProposalStatus::where('name', 'rejected')->first()->id,
            'status_change_comment' => $comment,
        ]);

        $this->auditLogService->log('rejected', 'proposals', $proposal->id, request());
    }

    public function assignReviewers(Proposal $proposal, array $reviewerIds, User $assignedBy): void
    {
        foreach ($reviewerIds as $reviewerId) {
            // Avoid duplicates
            if (!$proposal->reviewers()->where('reviewer_id', $reviewerId)->exists()) {
                $proposal->reviewers()->attach($reviewerId, [
                    'assigned_by' => $assignedBy->id,
                    'assigned_at' => now(),
                ]);
            }
        }

        $proposal->update(['status_id' => ProposalStatus::where('name', 'under_review')->first()->id]);

        $this->auditLogService->log('reviewers_assigned', 'proposals', $proposal->id, request());
    }
}
