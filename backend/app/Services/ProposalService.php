<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\User;
use App\Models\ProposalStatus;
use App\Models\Project;
use App\Jobs\RunProposalChecksJob;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ProposalService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private NotificationService $notificationService
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

        $this->auditLogService->log('submitted', 'proposals', $proposal->getKey(), request());
    }

    public function runChecks(Proposal $proposal, User $requestedBy): void
    {
        if ($proposal->status->name !== 'submitted') {
            throw ValidationException::withMessages([
                'status' => 'Only submitted proposals can be sent for checks.',
            ]);
        }

        $proposal->update([
            'status_id' => ProposalStatus::where('name', 'checking')->first()->id,
        ]);

        $this->auditLogService->log('checks_initiated', 'proposals', $proposal->getKey(), request());
        
        // Dispatch the background Job
        RunProposalChecksJob::dispatch($proposal);
    }

    public function approve(Proposal $proposal, User $approvedBy): void
    {
        if ($proposal->status->name === 'approved') {
             throw ValidationException::withMessages(['status' => 'Proposal is already approved.']);
        }

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

        // Checklist 4: Originality
        if ($proposal->originality_score === null) {
            throw ValidationException::withMessages([
                'originality' => "Plagiarism check must be completed before approval.",
            ]);
        }
        if ($proposal->originality_score < 70) {
            throw ValidationException::withMessages([
                'originality' => "Originality score too low ({$proposal->originality_score}%). Must be at least 70%.",
            ]);
        }

        DB::transaction(function() use ($proposal, $approvedBy) {
            $proposal->update([
                'status_id' => ProposalStatus::where('name', 'approved')->first()->id,
                'approved_by' => $approvedBy->getKey(),
                'approved_at' => now(),
            ]);

             $project = $proposal->project()->create([
                 'title' => $proposal->getAttribute('title'),
                 'start_date' => now(),
                 'end_date' => now()->addMonths(config('app.default_project_duration', 12)),
                 'total_budget' => $proposal->budget,
                 'status_id' => \App\Models\ProjectStatus::where('name', 'active')->first()->id ?? 1,
                 'pi_id' => $proposal->getAttribute('submitted_by'),
                 'academic_year_id' => $proposal->getAttribute('academic_year_id'),
                 'research_center_id' => $proposal->getAttribute('research_center_id'),
             ]);

            // Copy investigators to the project
            foreach ($proposal->investigators as $inv) {
                $project->investigators()->create([
                    'user_id' => $inv->user_id,
                    'name' => $inv->name,
                    'email' => $inv->email,
                    'institution' => $inv->institution,
                    'role_id' => $inv->role_id,
                    'status_id' => 1, // active
                ]);
            }

            // Sync Community Problem if linked via the Call
            if ($proposal->call && $proposal->call->community_problem_id) {
                $problem = $proposal->call->communityProblem;
                if ($problem) {
                    $problem->update([
                        'linked_project_id' => $project->id,
                        'status_id' => \App\Models\CommunityProblemStatus::where('name', 'claimed')->first()->id ?? 2
                    ]);
                }
            }
        });

        // Notify PI
        $this->notificationService->send(
            $proposal->submittedBy,
            'proposal_approved',
            "Congratulations! Your proposal '{$proposal->title}' has been approved and moved to projects.",
            "/app/proposals/{$proposal->id}"
        );

        $this->auditLogService->log('approved', 'proposals', $proposal->getKey(), request());
    }

    public function reject(Proposal $proposal, User $rejectedBy, string $comment): void
    {
        $proposal->update([
            'status_id' => ProposalStatus::where('name', 'rejected')->first()->id,
            'status_change_comment' => $comment,
        ]);

        $this->notificationService->send(
            $proposal->submittedBy,
            'proposal_rejected',
            "Your proposal '{$proposal->title}' has been rejected. Reason: {$comment}",
            "/app/proposals/{$proposal->id}"
        );

        $this->auditLogService->log('rejected', 'proposals', $proposal->getKey(), request());
    }

    public function assignReviewers(Proposal $proposal, array $reviewerIds, User $assignedBy): void
    {
        foreach ($reviewerIds as $reviewerId) {
            // Avoid duplicates
            if (!$proposal->reviewers()->where('reviewer_id', $reviewerId)->exists()) {
$proposal->reviewers()->attach($reviewerId, [
                     'assigned_by' => $assignedBy->getKey(),
                     'assigned_at' => now(),
                 ]);
            }
        }

        $proposal->update(['status_id' => ProposalStatus::where('name', 'under_review')->first()->id]);

        $this->auditLogService->log('reviewers_assigned', 'proposals', $proposal->getKey(), request());
    }
}
