<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ResearchCenter;

class ResearchCenterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ResearchCenter $researchCenter): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ResearchCenter $researchCenter): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ResearchCenter $researchCenter): bool
    {
        return $user->hasRole('super_admin');
    }
}
