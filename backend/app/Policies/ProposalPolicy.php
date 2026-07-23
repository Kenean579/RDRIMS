<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    public function view(User $user, Proposal $proposal): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($proposal->submitted_by === $user->id) {
            return true;
        }

        if ($proposal->reviewers()->where('reviewer_id', $user->id)->exists()) {
            return true;
        }

        $submittedBy = $proposal->relationLoaded('submittedBy')
            ? $proposal->getRelation('submittedBy')
            : $proposal->submittedBy;

        if ($submittedBy instanceof User && $user->sharesInstitutionWith($submittedBy)) {
            return $user->isAdmin();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->id !== null;
    }

    public function update(User $user, Proposal $proposal): bool
    {
        // Check if proposal is in draft status
        $isDraft = $proposal->status?->name === 'draft' 
            ?? ($proposal->status_id === ProposalStatus::where('name', 'draft')->first()?->id);
            
        if ($proposal->submitted_by === $user->id && $isDraft) {
            return true;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        $submittedBy = $proposal->relationLoaded('submittedBy')
            ? $proposal->getRelation('submittedBy')
            : $proposal->submittedBy;

        return $user->isAdmin() && $submittedBy instanceof User && $user->sharesInstitutionWith($submittedBy);
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        // Check if proposal is in draft status
        $isDraft = $proposal->status?->name === 'draft' 
            ?? ($proposal->status_id === ProposalStatus::where('name', 'draft')->first()?->id);
            
        return ($proposal->submitted_by === $user->id && $isDraft) || $user->hasRole('super_admin');
    }

    public function submit(User $user, Proposal $proposal): bool
    {
        // Check if proposal is in draft status
        $isDraft = $proposal->status?->name === 'draft' 
            ?? ($proposal->status_id === ProposalStatus::where('name', 'draft')->first()?->id);
            
        return $proposal->submitted_by === $user->id && $isDraft;
    }

    public function review(User $user, Proposal $proposal): bool
    {
        return $proposal->reviewers()->where('reviewer_id', $user->id)->exists();
    }

    public function assignReviewers(User $user, Proposal $proposal): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (! $user->isAdmin()) {
            return false;
        }

        $submittedBy = $proposal->relationLoaded('submittedBy')
            ? $proposal->getRelation('submittedBy')
            : $proposal->submittedBy;

        return $submittedBy instanceof User && $user->sharesInstitutionWith($submittedBy);
    }
}
