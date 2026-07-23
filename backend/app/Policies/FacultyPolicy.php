<?php

namespace App\Policies;

use App\Models\Faculty;
use App\Models\User;

class FacultyPolicy
{
    /**
     * Can view faculty list.
     * Super admin is denied; research_admin and below can view within their university.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('faculty.viewAny');
    }

    /**
     * Can view a specific faculty.
     * Must belong to the same university and have permission.
     */
    public function view(User $user, Faculty $faculty): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('faculty.view')
            && $this->sameUniversity($user, $faculty);
    }

    /**
     * Can create faculties.
     * Super admin is denied; must have permission.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('faculty.create');
    }

    /**
     * Can update faculties.
     * Must belong to the same university and have permission.
     */
    public function update(User $user, Faculty $faculty): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $this->sameUniversity($user, $faculty)
            && $user->hasPermission('faculty.update');
    }

    /**
     * Can delete faculties.
     * Must belong to the same university and have permission.
     */
    public function delete(User $user, Faculty $faculty): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $this->sameUniversity($user, $faculty)
            && $user->hasPermission('faculty.delete');
    }

    /**
     * Verify tenant ownership: faculty must belong to the user's university.
     */
    private function sameUniversity(User $user, Faculty $faculty): bool
    {
        return $user->university_id !== null
            && $faculty->campus?->university_id !== null
            && $user->university_id === $faculty->campus->university_id;
    }
}
