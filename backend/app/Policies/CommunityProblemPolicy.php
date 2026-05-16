<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CommunityProblem;

class CommunityProblemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CommunityProblem $communityProblem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CommunityProblem $communityProblem): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, CommunityProblem $communityProblem): bool
    {
        return $user->hasRole('super_admin');
    }
}