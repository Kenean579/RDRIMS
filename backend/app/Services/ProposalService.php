<?php

namespace App\Services;

use App\Enums\ProposalStatusEnum;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ProposalService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private NotificationService $notificationService,
    ) {}

    public function submit(Proposal $proposal, User $user): void
    {
        if ($proposal->status_id !== ProposalStatusEnum::DRAFT->value) {
            throw ValidationException::withMessages([
                'status' => 'Only draft proposals can be submitted.',
            ]);
        }

        if ($proposal->investigators()->count() === 0) {
            throw ValidationException::withMessages([
                'investigators' => 'At least one investigator is required.',
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatusEnum::SUBMITTED->value,
            'submitted_at' => now(),
            'submitted_by' => $user->id,
        ]);

        $this->auditLogService->log('submitted', 'proposals', $proposal->id, request());
        $this->notificationService->proposalSubmitted($user, $proposal->title, $proposal->id);
    }

    public function approve(Proposal $proposal, User $approvedBy): void
    {
        if ($proposal->status_id !== ProposalStatusEnum::UNDER_REVIEW->value) {
            throw ValidationException::withMessages([
                'status' => 'Only proposals under review can be approved.',
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatusEnum::APPROVED->value,
            'approved_by' => $approvedBy->id,
            'approved_at' => now(),
        ]);

        // Automatically create a project from approved proposal
        $project = $proposal->project()->create([
            'title' => $proposal->title,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'total_budget' => $proposal->budget,
            'status_id' => 1, // active
            'pi_id' => $proposal->submitted_by,
            'academic_year_id' => $proposal->academic_year_id,
        ]);

        $this->auditLogService->log('approved', 'proposals', $proposal->id, request());

        // Notify the submitter
        if ($proposal->submittedBy) {
            $this->notificationService->proposalApproved(
                $proposal->submittedBy,
                $proposal->title,
                $project->id
            );
        }
    }

    public function reject(Proposal $proposal, User $rejectedBy, string $comment): void
    {
        if (! in_array($proposal->status_id, [ProposalStatusEnum::SUBMITTED->value, ProposalStatusEnum::UNDER_REVIEW->value])) {
            throw ValidationException::withMessages([
                'status' => 'Only submitted or under-review proposals can be rejected.',
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatusEnum::REJECTED->value,
            'status_change_comment' => $comment,
        ]);

        $this->auditLogService->log('rejected', 'proposals', $proposal->id, request());

        // Notify the submitter
        if ($proposal->submittedBy) {
            $this->notificationService->proposalRejected(
                $proposal->submittedBy,
                $proposal->title,
                $comment
            );
        }
    }

    public function assignReviewers(Proposal $proposal, array $reviewerIds, User $assignedBy): void
    {
        foreach ($reviewerIds as $reviewerId) {
            $proposal->reviewers()->attach($reviewerId, [
                'assigned_by' => $assignedBy->id,
                'assigned_at' => now(),
            ]);
        }

        $proposal->update(['status_id' => ProposalStatusEnum::UNDER_REVIEW->value]);

        $this->auditLogService->log('reviewers_assigned', 'proposals', $proposal->id, request());

        // Notify each reviewer
        $reviewers = User::whereIn('id', $reviewerIds)->get();
        foreach ($reviewers as $reviewer) {
            $this->notificationService->reviewerAssigned($reviewer, $proposal->title, $proposal->id);
        }
    }

    public function runChecks(Proposal $proposal, User $user): void
    {
        // Dispatch automated checks
        \App\Jobs\RunProposalChecksJob::dispatch($proposal);

        $this->auditLogService->log('checks_initiated', 'proposals', $proposal->id, request());
    }
}
