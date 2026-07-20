<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if (! $user->isAdmin()) {
            return false;
        }

        return $user->sharesInstitutionWith($model);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if (! $user->isAdmin()) {
            return false;
        }

        return $user->sharesInstitutionWith($model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('super_admin');
    }
}
