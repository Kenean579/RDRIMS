<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MoU;

class MoUPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MoU $mou): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, MoU $mou): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, MoU $mou): bool
    {
        return $user->hasRole('super_admin');
    }
}