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
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Setting $setting): bool
    {
        return $user->hasRole('super_admin');
    }
}
