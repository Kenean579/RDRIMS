<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

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
        return $user->roles()->whereIn('name', ['researcher', 'admin'])->exists();
    }

    public function update(User $user, Publication $publication): bool
    {
        if ($user->roles()->where('name', 'admin')->exists()) return true;
        return $publication->authors()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Publication $publication): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }
}