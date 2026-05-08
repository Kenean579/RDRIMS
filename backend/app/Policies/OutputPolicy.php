<?php

namespace App\Policies;

use App\Models\Output;
use App\Models\User;

class OutputPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Output $output): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['researcher', 'admin', 'supervisor'])->exists();
    }

    public function update(User $user, Output $output): bool
    {
        if ($user->roles()->where('name', 'admin')->exists()) return true;

        return $output->participants()
            ->where('user_id', $user->id)
            ->whereHas('participantType', fn($q) => $q->whereIn('name', ['student', 'supervisor']))
            ->exists();
    }

    public function delete(User $user, Output $output): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }

    public function changeStatus(User $user, Output $output): bool
    {
        if ($user->roles()->where('name', 'admin')->exists()) return true;

        return $output->participants()
            ->where('user_id', $user->id)
            ->whereHas('participantType', fn($q) => $q->where('name', 'supervisor'))
            ->exists();
    }
}