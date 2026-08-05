<?php

namespace App\Policies;

use App\Models\Campus;
use App\Models\User;

class CampusPolicy
{
    /**
     * Can view campus list.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) return false;
        
        return $user->hasPermission('campus.viewAny');
    }

    /**
     * Can view a campus.
     */
    public function view(User $user, Campus $campus): bool
    {
        if ($user->hasRole('super_admin')) return false;
        return $user->hasPermission('campus.view') && $this->sameUniversity($user, $campus);
    }

    /**
     * Can create campuses.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) return false;
        return $user->hasPermission('campus.create');
    }

    /**
     * Can update campuses.
     */
    public function update(User $user, Campus $campus): bool
    {
        if ($user->hasRole('super_admin')) return false;
        return $this->sameUniversity($user, $campus) && $user->hasPermission('campus.update');
    }

    /**
     * Can delete campuses.
     */
    public function delete(User $user, Campus $campus): bool
    {
        if ($user->hasRole('super_admin')) return false;
        return $this->sameUniversity($user, $campus) && $user->hasPermission('campus.delete');
    }

    /**
     * Verify tenant ownership.
     */
    private function sameUniversity(User $user, Campus $campus): bool
    {
        return $user->university_id !== null && $user->university_id === $campus->university_id;
    }
}
