<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Proposal;
use App\Models\ProposalReviewer;
use App\Models\ReviewCriterion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function getAssignment(Proposal $proposal, int $reviewerId): ProposalReviewer
    {
        $pivot = ProposalReviewer::where('proposal_id', $proposal->id)
            ->where('reviewer_id', $reviewerId)
            ->first();

        if (!$pivot) {
            throw ValidationException::withMessages([
                'review' => ['You are not assigned as a reviewer for this proposal.'],
            ]);
        }

        return $pivot;
    }

    public function assertNotLocked(ProposalReviewer $pivot): void
    {
        if ($pivot->submitted_at !== null) {
            throw ValidationException::withMessages([
                'review' => ['This review has already been submitted and is locked. Contact the Research Office to request a revision.'],
            ]);
        }
    }

    /**
     * @param  array<int, array{criterion_id: int, score: int|float, comments?: string|null}>  $scores
     */
    public function validateScores(array $scores): void
    {
        $criteria = ReviewCriterion::whereIn('id', collect($scores)->pluck('criterion_id'))
            ->get()
            ->keyBy('id');

        $errors = [];

        foreach ($scores as $index => $scoreData) {
            $criterionId = $scoreData['criterion_id'];
            $criterion = $criteria->get($criterionId);

            if (!$criterion) {
                $errors["scores.{$index}.criterion_id"] = ['Invalid review criterion.'];
                continue;
            }

            $score = $scoreData['score'];

            if (!is_numeric($score) || $score < 0 || $score > $criterion->max_score) {
                $errors["scores.{$index}.score"] = [
                    "Score for \"{$criterion->name}\" must be between 0 and {$criterion->max_score}.",
                ];
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<int, array{criterion_id: int, score: int|float, comments?: string|null}>  $scores
     */
    public function saveScores(ProposalReviewer $pivot, array $scores): void
    {
        foreach ($scores as $scoreData) {
            $pivot->scores()->updateOrCreate(
                ['criterion_id' => $scoreData['criterion_id']],
                [
                    'score' => $scoreData['score'],
                    'comments' => $scoreData['comments'] ?? null,
                ]
            );
        }
    }

    public function submitReview(
        ProposalReviewer $pivot,
        array $scores,
        float $overallScore,
        ?string $overallComments,
        int $decisionId
    ): void {
        DB::transaction(function () use ($pivot, $scores, $overallScore, $overallComments, $decisionId) {
            $pivot->refresh();

            if ($pivot->submitted_at !== null) {
                throw ValidationException::withMessages([
                    'review' => ['This review has already been submitted and is locked.'],
                ]);
            }

            $this->validateScores($scores);
            $this->saveScores($pivot, $scores);

            $pivot->update([
                'overall_score' => $overallScore,
                'overall_comments' => $overallComments,
                'decision_id' => $decisionId,
                'submitted_at' => now(),
            ]);
        });
    }

    public function reopenReview(ProposalReviewer $pivot, User $actor): ProposalReviewer
    {
        if (!$actor->hasRole('super_admin', 'research_admin')) {
            throw ValidationException::withMessages([
                'review' => ['Only the Research Office can reopen a submitted review.'],
            ]);
        }

        if ($pivot->submitted_at === null) {
            throw ValidationException::withMessages([
                'review' => ['This review is not locked.'],
            ]);
        }

        $oldSubmittedAt = $pivot->submitted_at;

        $pivot->update(['submitted_at' => null]);

        $this->logAction('reviewer_review_reopened', $pivot->proposal, $actor, [
            'proposal_reviewer_id' => $pivot->id,
            'reviewer_id' => $pivot->reviewer_id,
            'previous_submitted_at' => $oldSubmittedAt?->toISOString(),
        ]);

        return $pivot->fresh();
    }

    public function logAction(string $action, Proposal $proposal, User $user, array $details = []): void
    {
        AuditLog::create(array_merge([
            'user_id' => $user->id,
            'university_id' => $user->university_id,
            'campus_id' => $user->campus_id,
            'faculty_id' => $user->faculty_id,
            'department_id' => $user->department_id,
            'research_center_id' => $user->research_center_id,
            'action' => $action,
            'table_name' => 'proposals',
            'record_id' => $proposal->id,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
            'old_values' => null,
            'new_values' => array_merge([
            'proposal_id' => $proposal->id,
            'user_id' => $user->id,
            ], $details),
        ]));

        Log::channel('reviewer')->info($action, array_merge([
            'proposal_id' => $proposal->id,
            'user_id' => $user->id,
        ], $details));
    }

    public function getReviewerStats(User $user): array
    {
        $reviews = ProposalReviewer::where('reviewer_id', $user->id)->get();
        $completed = $reviews->whereNotNull('submitted_at');

        return [
            'assigned_reviews' => $reviews->count(),
            'pending_reviews' => $reviews->whereNull('submitted_at')->count(),
            'completed_reviews' => $completed->count(),
            'average_score' => round((float) ($completed->avg('overall_score') ?? 0), 1),
        ];
    }

    public function getProposalReviewProgress(Proposal $proposal): array
    {
        $reviews = ProposalReviewer::where('proposal_id', $proposal->id)->get();
        $completed = $reviews->whereNotNull('submitted_at');

        return [
            'assigned' => $reviews->count(),
            'completed' => $completed->count(),
            'pending' => $reviews->whereNull('submitted_at')->count(),
            'average_score' => round((float) ($completed->avg('overall_score') ?? 0), 1),
        ];
    }

    /**
     * @param  Collection<int, int>  $proposalIds
     */
    public function getInstitutionalReviewProgress(Collection $proposalIds): array
    {
        if ($proposalIds->isEmpty()) {
            return [
                'assigned' => 0,
                'completed' => 0,
                'pending' => 0,
                'average_score' => 0,
            ];
        }

        $reviews = ProposalReviewer::whereIn('proposal_id', $proposalIds)->get();
        $completed = $reviews->whereNotNull('submitted_at');

        return [
            'assigned' => $reviews->count(),
            'completed' => $completed->count(),
            'pending' => $reviews->whereNull('submitted_at')->count(),
            'average_score' => round((float) ($completed->avg('overall_score') ?? 0), 1),
        ];
    }
}
