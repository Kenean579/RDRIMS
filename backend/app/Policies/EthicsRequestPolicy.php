<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EthicsRequest;

class EthicsRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('ethics_officer');
    }

    public function view(User $user, EthicsRequest $ethicsRequest): bool
    {
        return $user->isAdmin() || $user->hasRole('ethics_officer') || $ethicsRequest->submitted_by === $user->id;
    }

    public function update(User $user, EthicsRequest $ethicsRequest): bool
    {
        return $user->hasRole('ethics_officer') || $user->isAdmin();
    }
}
