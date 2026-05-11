<?php

namespace App\Policies;

use App\Models\CommunityProblem;
use App\Models\User;

class CommunityProblemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CommunityProblem $problem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CommunityProblem $problem): bool
    {
        return $user->roles()->where('name', 'admin')->exists() || $user->id === $problem->claimed_by;
    }

    public function delete(User $user, CommunityProblem $problem): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }

    public function claim(User $user, CommunityProblem $problem): bool
    {
        return $user->roles()->whereIn('name', ['researcher', 'admin'])->exists() && !$problem->claimed_by;
    }

    public function complete(User $user, CommunityProblem $problem): bool
    {
        return $user->id === $problem->claimed_by || $user->roles()->where('name', 'admin')->exists();
    }

    public function addFeedback(User $user, CommunityProblem $problem): bool
    {
        return $user->id === $problem->submitted_by ||
               $user->id === $problem->claimed_by ||
               $user->roles()->where('name', 'admin')->exists();
    }
}