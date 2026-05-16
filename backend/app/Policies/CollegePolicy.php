<?php

namespace App\Policies;

use App\Models\User;
use App\Models\College;

class CollegePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, College $college): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, College $college): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, College $college): bool
    {
        return $user->hasRole('super_admin');
    }
}
