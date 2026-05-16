<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ThematicArea;

class ThematicAreaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ThematicArea $thematicArea): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ThematicArea $thematicArea): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ThematicArea $thematicArea): bool
    {
        return $user->hasRole('super_admin');
    }
}
