<?php

namespace App\Policies;

use App\Models\ProposalReviewer;
use App\Models\User;

class ProposalReviewerPolicy
{
    /**
     * Determine if user can view reviewer assignments for a proposal.
     * Only admins or the assigned reviewer can view.
     */
    public function view(User $user, ProposalReviewer $proposalReviewer): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Reviewer can view their own assignment
        if ($proposalReviewer->reviewer_id === $user->id) {
            return true;
        }

        // Admin can view if in same institution
        if ($user->isAdmin() && $proposalReviewer->proposal) {
            $submittedBy = $proposalReviewer->proposal->relationLoaded('submittedBy')
                ? $proposalReviewer->proposal->getRelation('submittedBy')
                : $proposalReviewer->proposal->submittedBy;

            if ($submittedBy instanceof User) {
                return $user->sharesInstitutionWith($submittedBy);
            }
        }

        return false;
    }

    /**
     * Determine if user can view reviewer assignment list for a proposal.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('super_admin');
    }

    /**
     * Determine if user can create reviewer assignments.
     * Only admins within the institution can assign reviewers.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('super_admin');
    }

    /**
     * Determine if user can update reviewer assignment (e.g., reopen review).
     * Only admins can reopen submitted reviews.
     */
    public function update(User $user, ProposalReviewer $proposalReviewer): bool
    {
        if (!$user->isAdmin() && !$user->hasRole('super_admin')) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Research admin can only reopen within their institution
        if ($proposalReviewer->proposal) {
            $submittedBy = $proposalReviewer->proposal->relationLoaded('submittedBy')
                ? $proposalReviewer->proposal->getRelation('submittedBy')
                : $proposalReviewer->proposal->submittedBy;

            if ($submittedBy instanceof User) {
                return $user->sharesInstitutionWith($submittedBy);
            }
        }

        return false;
    }

    /**
     * Determine if user can delete reviewer assignment.
     */
    public function delete(User $user, ProposalReviewer $proposalReviewer): bool
    {
        return $this->update($user, $proposalReviewer);
    }

    /**
     * Determine if reviewer can submit their review.
     */
    public function submit(User $user, ProposalReviewer $proposalReviewer): bool
    {
        // Only the assigned reviewer can submit
        if ($proposalReviewer->reviewer_id !== $user->id) {
            return false;
        }

        // Cannot submit if already submitted
        if ($proposalReviewer->submitted_at !== null) {
            return false;
        }

        // Verify proposal exists and is in review status
        if (!$proposalReviewer->proposal || !$proposalReviewer->proposal->status) {
            return false;
        }

        $reviewStatuses = ['under_review', 'submitted', 'finance_check', 'ethics_pending'];
        return in_array($proposalReviewer->proposal->status->name, $reviewStatuses);
    }
}
