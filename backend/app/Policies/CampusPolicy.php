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
        return $user->isAdmin();
    }

    /**
     * Can view a campus.
     */
    public function view(User $user, Campus $campus): bool
    {
        return $this->sameUniversity($user, $campus);
    }

    /**
     * Can create campuses.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(
            'super_admin',
            'research_admin'
        );
    }

    /**
     * Can update campuses.
     */
    public function update(User $user, Campus $campus): bool
    {
        return $this->sameUniversity($user, $campus)
            && $user->hasRole(
                'super_admin',
                'research_admin'
            );
    }

    /**
     * Can delete campuses.
     */
    public function delete(User $user, Campus $campus): bool
    {
        return $this->sameUniversity($user, $campus)
            && $user->hasRole(
                'super_admin',
                'research_admin'
            );
    }

    /**
     * Verify tenant ownership.
     */
    private function sameUniversity(User $user, Campus $campus): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->university_id !== null
            && $user->university_id === $campus->university_id;
    }
}
