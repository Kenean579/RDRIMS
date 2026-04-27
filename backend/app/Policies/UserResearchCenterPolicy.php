<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserResearchCenter;
use Illuminate\Auth\Access\Response;

class UserResearchCenterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserResearchCenter $userResearchCenter): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserResearchCenter $userResearchCenter): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserResearchCenter $userResearchCenter): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserResearchCenter $userResearchCenter): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserResearchCenter $userResearchCenter): bool
    {
        return false;
    }
}
