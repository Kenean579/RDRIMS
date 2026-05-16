<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isAdmin() || 
               $project->pi_id === $user->id || 
               $project->investigators()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->pi_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole('super_admin');
    }
}