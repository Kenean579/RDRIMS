<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine if user can view task
     */
    public function view(User $user, Task $task): bool
    {
        $project = $task->milestone->project;
        
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Project members can view tasks
        if ($project->isMember($user->id)) {
            return true;
        }

        // Assigned user can view
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Admin within same institution can view
        if ($user->isAdmin() && $project->pi) {
            return $user->sharesInstitutionWith($project->pi);
        }

        return false;
    }

    /**
     * Determine if user can create tasks
     */
    public function create(User $user, $milestone): bool
    {
        $project = $milestone->project;
        
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI and investigators can create tasks
        if ($project->isMember($user->id)) {
            return true;
        }

        // Admin within same institution can create
        if ($user->isAdmin() && $project->pi) {
            return $user->sharesInstitutionWith($project->pi);
        }

        return false;
    }

    /**
     * Determine if user can update task
     */
    public function update(User $user, Task $task): bool
    {
        $project = $task->milestone->project;
        
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI can update any task
        if ($project->pi_id === $user->id) {
            return true;
        }

        // Assigned user can update their task
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Admin within same institution can update
        if ($user->isAdmin() && $project->pi) {
            return $user->sharesInstitutionWith($project->pi);
        }

        return false;
    }

    /**
     * Determine if user can delete task
     */
    public function delete(User $user, Task $task): bool
    {
        $project = $task->milestone->project;
        
        // Cannot delete completed tasks
        if ($task->status?->name === 'completed') {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only PI can delete tasks
        return $project->pi_id === $user->id;
    }
}
