<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        $project = $task->milestone->project;
        return $user->id === $project->pi_id || $user->roles()->where('name', 'admin')->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        $project = $task->milestone->project;
        return $user->id === $project->pi_id || $user->roles()->where('name', 'admin')->exists();
    }

    public function delete(User $user, Task $task): bool
    {
        $project = $task->milestone->project;
        return $user->id === $project->pi_id || $user->roles()->where('name', 'admin')->exists();
    }
}