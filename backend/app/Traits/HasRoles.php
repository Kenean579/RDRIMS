<?php

namespace App\Traits;

trait HasRoles
{
    public function hasRole(string ...$roles): bool
    {
        $userRoles = $this->roles->pluck('name')->toArray();
        return ! empty(array_intersect($roles, $userRoles));
    }

    /**
     * Check if user has any of the given roles (accepts an array).
     */
    public function hasAnyRole(array $roles): bool
    {
        $userRoles = $this->roles->pluck('name')->toArray();
        return ! empty(array_intersect($roles, $userRoles));
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles->contains(function ($role) use ($permission) {
            return $role->permissions->contains('name', $permission);
        });
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(
            'super_admin',
            'research_admin',
            'university_admin',
            'campus_admin',
            'faculty_admin',
            'department_head',
            'director',
            'finance_officer',
            'ethics_officer'
        );
    }
}
