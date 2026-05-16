<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Call;

class CallPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Call $call): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Call $call): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Call $call): bool
    {
        return $user->hasRole('super_admin');
    }
}
