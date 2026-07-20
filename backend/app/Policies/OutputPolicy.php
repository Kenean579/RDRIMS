<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Output;

class OutputPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Output $output): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $output->participants()->where('users.id', $user->id)->exists()
            || $output->participants()->hierarchical($user, 'id')->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Output $output): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($output->submitted_by === $user->id) {
            return true;
        }

        $project = $output->relationLoaded('project') ? $output->getRelation('project') : $output->project;
        $pi = $project?->relationLoaded('pi') ? $project->getRelation('pi') : $project?->pi;

        return $user->isAdmin() && $pi instanceof User && $user->sharesInstitutionWith($pi);
    }

    public function delete(User $user, Output $output): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $output->submitted_by === $user->id;
    }

    public function changeStatus(User $user, Output $output): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $output->submitted_by === $user->id;
    }
}
