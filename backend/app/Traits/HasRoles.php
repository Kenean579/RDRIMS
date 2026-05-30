<?php

namespace App\Traits;

trait HasRoles
{
    public function hasRole(string ...$roles): bool
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
        return $this->hasRole('super_admin', 'research_admin', 'admin');
    }
}
