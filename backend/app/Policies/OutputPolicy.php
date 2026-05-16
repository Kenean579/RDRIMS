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
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Output $output): bool
    {
        $project = $output->project;
        return $user->isAdmin() || $project->pi_id === $user->id || $output->submitted_by === $user->id;
    }

    public function delete(User $user, Output $output): bool
    {
        return $user->isAdmin() || $output->submitted_by === $user->id;
    }

    public function changeStatus(User $user, Output $output): bool
    {
        return $user->isAdmin() || $output->submitted_by === $user->id;
    }
}