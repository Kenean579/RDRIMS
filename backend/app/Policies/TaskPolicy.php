<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true; // Any team member can create tasks, or restricted by project
    }

    public function update(User $user, Task $task): bool
    {
        $project = $task->project;
        return $user->isAdmin() || $project->pi_id === $user->id || $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        $project = $task->project;
        return $user->isAdmin() || $project->pi_id === $user->id;
    }
}