<?php

namespace App\Policies;

use App\Models\MoU;
use App\Models\User;

class MoUPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'industry_officer', 'researcher'])->exists();
    }

    public function view(User $user, MoU $moU): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'industry_officer'])->exists();
    }

    public function update(User $user, MoU $moU): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'industry_officer'])->exists();
    }

    public function delete(User $user, MoU $moU): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }
}