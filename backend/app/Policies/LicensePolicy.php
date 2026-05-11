<?php

namespace App\Policies;

use App\Models\License;
use App\Models\User;

class LicensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['researcher', 'admin', 'tech_transfer_officer'])->exists();
    }

    public function view(User $user, License $license): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'tech_transfer_officer'])->exists();
    }

    public function update(User $user, License $license): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'tech_transfer_officer'])->exists();
    }

    public function delete(User $user, License $license): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }
}