<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EthicsRequest;

class EthicsRequestPolicy
{
    /**
     * Determine if user can view any ethics requests
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('researcher');
    }

    /**
     * Determine if user can view a specific ethics request
     */
    public function view(User $user, EthicsRequest $ethicsRequest): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $submitter = $ethicsRequest->proposal?->submittedBy;
        
        // Submitter can view their own request
        if ($submitter && $submitter->id === $user->id) {
            return true;
        }

        // Ethics officer or admin from same institution can view
        if ($user->isAdmin()) {
            return $submitter ? $user->sharesInstitutionWith($submitter) : false;
        }

        return false;
    }

    /**
     * Determine if user can create an ethics request
     */
    public function create(User $user): bool
    {
        // Researchers can create ethics requests for their proposals
        return $user->isAdmin() || $user->hasRole('researcher');
    }

    /**
     * Determine if user can update an ethics request (edit draft/revision)
     */
    public function update(User $user, EthicsRequest $ethicsRequest): bool
    {
        // Super admin can update all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $submitter = $ethicsRequest->proposal?->submittedBy;

        // Can only update if in editable status
        if (!$ethicsRequest->canEdit()) {
            return false;
        }

        // Submitter can update their own request if it's draft or needs revision
        if ($submitter && $submitter->id === $user->id) {
            return true;
        }

        // Admin from same institution can update
        if ($user->isAdmin()) {
            return $submitter ? $user->sharesInstitutionWith($submitter) : false;
        }

        return false;
    }

    /**
     * Determine if user can delete an ethics request
     */
    public function delete(User $user, EthicsRequest $ethicsRequest): bool
    {
        // Super admin can delete all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $submitter = $ethicsRequest->proposal?->submittedBy;

        // Cannot delete reviewed requests
        if ($ethicsRequest->isReviewed()) {
            return false;
        }

        // Submitter can delete their own draft/revision request
        if ($submitter && $submitter->id === $user->id) {
            return true;
        }

        // Admin from same institution can delete draft/revision
        if ($user->isAdmin()) {
            return $submitter ? $user->sharesInstitutionWith($submitter) : false;
        }

        return false;
    }

    /**
     * Determine if user can submit an ethics request for review
     */
    public function submit(User $user, EthicsRequest $ethicsRequest): bool
    {
        $submitter = $ethicsRequest->proposal?->submittedBy;

        // Submitter can submit their own request if it's in editable status
        if ($submitter && $submitter->id === $user->id && $ethicsRequest->canEdit()) {
            return true;
        }

        // Admin from same institution can submit on behalf
        if ($user->isAdmin()) {
            return $submitter ? $user->sharesInstitutionWith($submitter) : false;
        }

        // Super admin can submit
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if user can mark submitted to IRB
     */
    public function markSubmitted(User $user, EthicsRequest $ethicsRequest): bool
    {
        $submitter = $ethicsRequest->proposal?->submittedBy;

        // Submitter can mark their own
        if ($submitter && $submitter->id === $user->id) {
            return true;
        }

        // Admin or ethics officer from same institution can mark
        if ($user->isAdmin()) {
            return $submitter ? $user->sharesInstitutionWith($submitter) : false;
        }

        return $user->hasRole('super_admin');
    }

    /**
     * Determine if user can make a decision (approve/reject/revision)
     */
    public function decide(User $user, EthicsRequest $ethicsRequest): bool
    {
        // Only ethics officers or admins can make decisions
        if (!$user->isAdmin()) {
            return false;
        }

        // Check tenant isolation for non-super-admins
        if (!$user->hasRole('super_admin')) {
            $submitter = $ethicsRequest->proposal?->submittedBy;
            if ($submitter && !$user->sharesInstitutionWith($submitter)) {
                return false;
            }
        }

        // Older generated requests were left with submitted_to_irb=false even
        // though their PDF had already been placed in the Ethics review queue.
        $isReadyForReview = $ethicsRequest->submitted_to_irb
            || filled($ethicsRequest->generated_pdf_path);

        return $isReadyForReview && !$ethicsRequest->isReviewed();
    }

    /**
     * Determine if user can approve
     */
    public function approve(User $user, EthicsRequest $ethicsRequest): bool
    {
        return $this->decide($user, $ethicsRequest);
    }

    /**
     * Determine if user can reject
     */
    public function reject(User $user, EthicsRequest $ethicsRequest): bool
    {
        return $this->decide($user, $ethicsRequest);
    }

    /**
     * Determine if user can request revision
     */
    public function requestRevision(User $user, EthicsRequest $ethicsRequest): bool
    {
        return $this->decide($user, $ethicsRequest);
    }
}
