<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Publication;

class PublicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Publication $publication): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Publication $publication): bool
    {
        return $user->isAdmin() || $publication->authors()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Publication $publication): bool
    {
        return $user->isAdmin() || $publication->authors()->where('user_id', $user->id)->exists();
    }
}