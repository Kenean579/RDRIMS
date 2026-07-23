<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    /**
     * Can view department list.
     * Super admin is denied; research_admin and below can view within their university.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('department.viewAny');
    }

    /**
     * Can view a specific department.
     * Must belong to the same university and have permission.
     */
    public function view(User $user, Department $department): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('department.view')
            && $this->sameUniversity($user, $department);
    }

    /**
     * Can create departments.
     * Super admin is denied; must have permission.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermission('department.create');
    }

    /**
     * Can update departments.
     * Must belong to the same university and have permission.
     */
    public function update(User $user, Department $department): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $this->sameUniversity($user, $department)
            && $user->hasPermission('department.update');
    }

    /**
     * Can delete departments.
     * Must belong to the same university and have permission.
     */
    public function delete(User $user, Department $department): bool
    {
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $this->sameUniversity($user, $department)
            && $user->hasPermission('department.delete');
    }

    /**
     * Verify tenant ownership: department must belong to the user's university.
     * Traverses: Department → Faculty → Campus → University
     */
    private function sameUniversity(User $user, Department $department): bool
    {
        return $user->university_id !== null
            && $department->faculty?->campus?->university_id !== null
            && $user->university_id === $department->faculty->campus->university_id;
    }
}
