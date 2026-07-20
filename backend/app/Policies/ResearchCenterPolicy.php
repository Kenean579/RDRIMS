<?php

namespace App\Policies;

use App\Models\ResearchCenter;
use App\Models\User;

class ResearchCenterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    public function view(User $user, ResearchCenter $researchCenter): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $userUniversityId = $user->resolvedUniversityId();
        $centerUniversityId = $researchCenter->parent_university_id ?? $researchCenter->university_id ?? null;

        return $userUniversityId !== null && $centerUniversityId !== null && (int) $centerUniversityId === (int) $userUniversityId;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ResearchCenter $researchCenter): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $userUniversityId = $user->resolvedUniversityId();
        $centerUniversityId = $researchCenter->parent_university_id ?? $researchCenter->university_id ?? null;

        return $user->isAdmin() && $userUniversityId !== null && $centerUniversityId !== null && (int) $centerUniversityId === (int) $userUniversityId;
    }

    public function delete(User $user, ResearchCenter $researchCenter): bool
    {
        return $this->update($user, $researchCenter);
    }
}
