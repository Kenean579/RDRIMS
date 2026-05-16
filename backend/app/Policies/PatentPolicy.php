<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patent;

class PatentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Patent $patent): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Patent $patent): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Patent $patent): bool
    {
        return $user->hasRole('super_admin');
    }
}