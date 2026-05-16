<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Proposal;

class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Proposal $proposal): bool
    {
        return $user->isAdmin() || 
               $proposal->submitted_by === $user->id || 
               $proposal->reviewers()->where('reviewer_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('researcher', 'student');
    }

    public function update(User $user, Proposal $proposal): bool
    {
        return ($proposal->submitted_by === $user->id && $proposal->status_id === 1) || $user->isAdmin();
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return ($proposal->submitted_by === $user->id && $proposal->status_id === 1) || $user->hasRole('super_admin');
    }

    public function submit(User $user, Proposal $proposal): bool
    {
        return $proposal->submitted_by === $user->id && $proposal->status_id === 1;
    }

    public function review(User $user, Proposal $proposal): bool
    {
        return $proposal->reviewers()->where('reviewer_id', $user->id)->exists();
    }

    public function assignReviewers(User $user): bool
    {
        return $user->isAdmin();
    }
}
