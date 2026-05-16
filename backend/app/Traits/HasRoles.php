<?php

namespace App\Traits;

trait HasRoles
{
    public function hasRole(string ...$roles): bool
    {
        $userRoles = $this->roles()->pluck('name')->toArray();
        return ! empty(array_intersect($roles, $userRoles));
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $permission))
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin', 'research_admin');
    }
}
