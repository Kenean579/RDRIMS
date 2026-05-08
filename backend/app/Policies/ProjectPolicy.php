<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['researcher', 'admin', 'reviewer', 'finance_officer'])->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->pi_id || $user->roles()->where('name', 'admin')->exists();
    }

    public function create(User $user): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->pi_id || $user->roles()->where('name', 'admin')->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }
}