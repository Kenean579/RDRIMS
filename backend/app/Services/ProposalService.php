<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ProposalService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private NotificationService $notificationService,
    ) {}

    /**
     * Helper: resolve a status ID by name from the database.
     */
    private function statusId(string $name): int
    {
        return ProposalStatus::where('name', $name)->value('id');
    }

    public function submit(Proposal $proposal, User $user): void
    {
        $draftStatusId = $this->statusId('draft');
        
        if ($proposal->status_id !== $draftStatusId) {
            throw ValidationException::withMessages([
                'status' => 'Only draft proposals can be submitted.',
            ]);
        }

        if ($proposal->investigators()->count() === 0) {
            throw ValidationException::withMessages([
                'investigators' => 'At least one investigator is required.',
            ]);
        }

        // SECURITY FIX: Verify the user owns this proposal before submission
        if ($proposal->submitted_by !== $user->id) {
            throw ValidationException::withMessages([
                'authorization' => 'You can only submit your own proposals.',
            ]);
        }

        $proposal->status_id = $this->statusId('submitted');
        $proposal->submitted_at = now();
        $proposal->save();

        $this->auditLogService->log('submitted', 'proposals', $proposal->id, request());
        $this->notificationService->proposalSubmitted($user, $proposal->title, $proposal->id);

        // Notify Call Creator
        $call = $proposal->call;
        if ($call && $call->createdBy && $call->createdBy->id !== $user->id) {
            $this->notificationService->proposalReceived($call->createdBy, $proposal->title, $proposal->id, $user->name);
        }

        // Notify Research Admins (Research Directorate Admins) of the university
        $universityId = $call?->university_id ?: $user->university_id;
        if ($universityId) {
            $researchAdmins = User::whereHas('roles', fn($q) => $q->where('name', 'research_admin'))
                ->where(function($q) use ($universityId) {
                    $q->where('university_id', $universityId)
                      ->orWhereNull('university_id');
                })
                ->get();
            foreach ($researchAdmins as $admin) {
                if ($admin->id !== $user->id) {
                    $this->notificationService->proposalReceived($admin, $proposal->title, $proposal->id, $user->name);
                }
            }
        }
    }

    public function approve(Proposal $proposal, User $approvedBy): void
    {
        $allowedStatuses = [
            $this->statusId('under_review'),
            $this->statusId('finance_check'),
            $this->statusId('ethics_pending'),
        ];

        if (!in_array($proposal->status_id, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'status' => 'Only proposals under review, finance check, or ethics pending can be approved.',
            ]);
        }

        // 1. All reviews must be completed
        $reviewers = $proposal->reviewers;
        if ($reviewers->isEmpty()) {
            throw ValidationException::withMessages([
                'status' => 'Reviewer assignment is incomplete. Please assign reviewers first.',
            ]);
        }
        $pendingReviews = $proposal->reviewers()->wherePivotNull('submitted_at')->count();
        if ($pendingReviews > 0) {
            throw ValidationException::withMessages([
                'status' => "Cannot approve proposal. There are still {$pendingReviews} pending peer reviews.",
            ]);
        }

        // 2. Finance Approved (if required)
        $autoApproveBelowBudget = (float) (\App\Models\Setting::where('key', 'auto_approve_below_budget')->value('value') ?? 100000);
        $financeRequired = (float) $proposal->budget >= $autoApproveBelowBudget;
        if ($financeRequired) {
            $latestFinanceCheck = $proposal->financeChecks()->latest()->first();
            if (!$latestFinanceCheck) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot approve proposal. Budget evaluation / Finance check has not been requested.',
                ]);
            }
            $approvedStatusId = \App\Models\FinanceCheckStatus::where('name', 'approved')->value('id');
            if ($latestFinanceCheck->status_id !== $approvedStatusId) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot approve proposal. Finance check is not approved.',
                ]);
            }
        }

        // 3. Ethics Approved (if required)
        $ethicsRequired = \App\Models\Setting::where('key', 'ethics_required')->value('value') === 'true';
        if ($ethicsRequired) {
            $latestEthicsRequest = $proposal->ethicsRequests()->latest()->first();
            if (!$latestEthicsRequest) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot approve proposal. Ethics clearance / IRB request has not been generated.',
                ]);
            }
            $approvedStatusId = \App\Models\EthicsApprovalStatus::where('name', 'approved')->value('id');
            if ($latestEthicsRequest->approval_status_id !== $approvedStatusId) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot approve proposal. Ethics clearance is not approved.',
                ]);
            }
        }

        // SECURITY FIX: Use explicit assignment for protected fields
        $proposal->status_id = $this->statusId('approved');
        $proposal->approved_by = $approvedBy->id;
        $proposal->approved_at = now();
        $proposal->save();

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
        $submittedId = $this->statusId('submitted');
        $underReviewId = $this->statusId('under_review');

        if (! in_array($proposal->status_id, [$submittedId, $underReviewId])) {
            throw ValidationException::withMessages([
                'status' => 'Only submitted or under-review proposals can be rejected.',
            ]);
        }

        // SECURITY FIX: Use explicit assignment for protected fields
        $proposal->status_id = $this->statusId('rejected');
        $proposal->status_change_comment = $comment;
        $proposal->save();

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
        if (is_null($proposal->originality_score)) {
            throw ValidationException::withMessages([
                'status' => 'Originality/plagiarism check must be completed before assigning reviewers.',
            ]);
        }

        $proposal->reviewers()->syncWithoutDetaching(array_fill_keys($reviewerIds, [
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
        ]));

        // SECURITY FIX: Use explicit assignment for protected field
        $proposal->status_id = $this->statusId('under_review');
        $proposal->save();

        $this->auditLogService->log('reviewers_assigned', 'proposals', $proposal->id, request());

        // Notify each reviewer
        $reviewers = User::whereIn('id', $reviewerIds)->get();
        foreach ($reviewers as $reviewer) {
            $this->notificationService->reviewerAssigned($reviewer, $proposal->title, $proposal->id);
        }
    }

    public function runChecks(Proposal $proposal, User $user): void
    {
        // SECURITY FIX: Use explicit assignment for protected field
        $proposal->status_id = $this->statusId('checking');
        $proposal->save();

        // Dispatch automated checks
        \App\Jobs\RunProposalChecksJob::dispatch($proposal);

        $this->auditLogService->log('checks_initiated', 'proposals', $proposal->id, request());
    }
}

