<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;

class MilestonePolicy
{
    /**
     * Determine if user can view milestone
     */
    public function view(User $user, Milestone $milestone): bool
    {
        $project = $milestone->project;
        
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Project members can view
        if ($project->isMember($user->id)) {
            return true;
        }

        // Admin within same institution can view
        if ($user->isAdmin() && $project->pi) {
            return $user->sharesInstitutionWith($project->pi);
        }

        return false;
    }

    /**
     * Determine if user can create milestones
     */
    public function create(User $user, $project): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI can create milestones
        if ($project->pi_id === $user->id) {
            return true;
        }

        // Admin within same institution can create
        if ($user->isAdmin() && $project->pi) {
            return $user->sharesInstitutionWith($project->pi);
        }

        return false;
    }

    /**
     * Determine if user can update milestone
     */
    public function update(User $user, Milestone $milestone): bool
    {
        $project = $milestone->project;
        
        // Cannot update completed milestones
        if ($milestone->status?->name === 'completed') {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI can update
        if ($project->pi_id === $user->id) {
            return true;
        }

        // Admin within same institution can update
        if ($user->isAdmin() && $project->pi) {
            return $user->sharesInstitutionWith($project->pi);
        }

        return false;
    }

    /**
     * Determine if user can delete milestone
     */
    public function delete(User $user, Milestone $milestone): bool
    {
        $project = $milestone->project;
        
        // Cannot delete completed milestones
        if ($milestone->status?->name === 'completed') {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only PI can delete
        return $project->pi_id === $user->id;
    }
}
