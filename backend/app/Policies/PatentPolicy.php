<?php

namespace App\Policies;

use App\Models\Patent;
use App\Models\User;

class PatentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['researcher', 'admin', 'tech_transfer_officer'])->exists();
    }

    public function view(User $user, Patent $patent): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'tech_transfer_officer'])->exists();
    }

    public function update(User $user, Patent $patent): bool
    {
        if ($user->roles()->whereIn('name', ['admin', 'tech_transfer_officer'])->exists()) return true;
        if ($patent->project && $user->id === $patent->project->pi_id) return true;
        return false;
    }

    public function delete(User $user, Patent $patent): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }
}