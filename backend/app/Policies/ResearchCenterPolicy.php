<?php

namespace App\Policies;

use App\Models\ResearchCenter;
use App\Models\User;

class ResearchCenterPolicy
{
    /**
     * Can view research center list.
     * Super admin is denied; research_admin and below can view within their university.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('research_center.viewAny');
    }

    /**
     * Can view a specific research center.
     * Must belong to the same university and have permission.
     */
    public function view(User $user, ResearchCenter $researchCenter): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('research_center.view')
            && $this->sameUniversity($user, $researchCenter);
    }

    /**
     * Can create research centers.
     * Super admin is denied; must have permission.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('research_center.create');
    }

    /**
     * Can update research centers.
     * Must belong to the same university and have permission.
     */
    public function update(User $user, ResearchCenter $researchCenter): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $this->sameUniversity($user, $researchCenter)
            && $user->hasPermission('research_center.update');
    }

    /**
     * Can delete research centers.
     * Must belong to the same university and have permission.
     */
    public function delete(User $user, ResearchCenter $researchCenter): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $this->sameUniversity($user, $researchCenter)
            && $user->hasPermission('research_center.delete');
    }

    /**
     * Verify tenant ownership: research center must belong to the user's university.
     */
    private function sameUniversity(User $user, ResearchCenter $researchCenter): bool
    {
        return $user->university_id !== null
            && $researchCenter->parent_university_id !== null
            && $user->university_id === $researchCenter->parent_university_id;
    }
}
