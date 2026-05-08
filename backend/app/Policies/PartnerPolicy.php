<?php

namespace App\Policies;

use App\Models\Partner;
use App\Models\User;

class PartnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'industry_officer', 'researcher'])->exists();
    }

    public function view(User $user, Partner $partner): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'industry_officer'])->exists();
    }

    public function update(User $user, Partner $partner): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'industry_officer'])->exists();
    }

    public function delete(User $user, Partner $partner): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }
}