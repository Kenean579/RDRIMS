<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Setting;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Setting $setting): bool
    {
        // Super admin can view all settings; other admins can view institution-specific via scope
        return $user->hasRole('super_admin') || $user->hasAnyInstitutionRole();
    }

    public function create(User $user): bool
    {
        // Only super_admin can create global settings (no institution association)
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Setting $setting): bool
    {
        // Super admin can update any global setting; institution admins can update only their scoped settings
        if ($user->hasRole('super_admin')) {
            return true;
        }
        // Verify the setting belongs to the user's hierarchy scope
        return $user->hasScopeForSetting($setting);
    }

    public function delete(User $user, Setting $setting): bool
    {
        // Only super_admin may delete global settings
        if ($user->hasRole('super_admin')) {
            return true;
        }
        // Institution admins cannot delete global defaults; they may delete overrides they own
        return $user->hasScopeForSetting($setting);
    }
}
