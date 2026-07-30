<?php

namespace App\Traits;

trait HasRoles
{
    public function hasRole(string ...$roles): bool
    {
        $userRoles = $this->roles->pluck('name')->toArray();
        return !empty(array_intersect($roles, $userRoles));
    }

    /**
     * Check if user has any of the given roles (accepts an array).
     */
    public function hasAnyRole(array $roles): bool
    {
        $userRoles = $this->roles->pluck('name')->toArray();
        return !empty(array_intersect($roles, $userRoles));
    }

    /**
     * Assign a role to the user by role name.
     * Creates the role if it doesn't exist.
     * 
     * @param string $roleName
     * @return void
     */
    public function assignRole(string $roleName): void
    {
        $role = \App\Models\Role::firstOrCreate(['name' => $roleName]);
        
        // Only attach if not already assigned
        if (!$this->roles->contains('id', $role->id)) {
            $this->roles()->attach($role->id);
            
            // Refresh the roles relationship to reflect the change
            $this->load('roles');
        }
    }

    /**
     * Remove a role from the user by role name.
     * 
     * @param string $roleName
     * @return void
     */
    public function removeRole(string $roleName): void
    {
        $role = \App\Models\Role::where('name', $roleName)->first();
        
        if ($role) {
            $this->roles()->detach($role->id);
            
            // Refresh the roles relationship to reflect the change
            $this->load('roles');
        }
    }

    /**
     * Sync user roles (replaces all existing roles).
     * 
     * @param array $roleNames
     * @return void
     */
    public function syncRoles(array $roleNames): void
    {
        $roleIds = \App\Models\Role::whereIn('name', $roleNames)->pluck('id')->toArray();
        $this->roles()->sync($roleIds);
        $this->load('roles');
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
