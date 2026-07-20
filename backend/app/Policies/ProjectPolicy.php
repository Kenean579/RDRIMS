<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    public function view(User $user, Project $project): bool
    {
        if ($project->pi_id === $user->id || $project->investigators()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if (! $user->isAdmin()) {
            return false;
        }

        $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
        return $pi instanceof User && $user->sharesInstitutionWith($pi);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        if ($project->pi_id === $user->id) {
            return true;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
        return $user->isAdmin() && $pi instanceof User && $user->sharesInstitutionWith($pi);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole('super_admin');
    }
}
