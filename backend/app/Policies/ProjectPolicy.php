<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine if user can view any projects
     */
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    /**
     * Determine if user can view a project
     */
    public function view(User $user, Project $project): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI can view their project
        if ($project->pi_id === $user->id) {
            return true;
        }

        // Project investigators can view
        if ($project->investigators()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Admin within same institution can view
        if ($user->isAdmin()) {
            $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
            return $pi instanceof User && $user->sharesInstitutionWith($pi);
        }

        return false;
    }

    /**
     * Determine if user can create projects
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('super_admin');
    }

    /**
     * Determine if user can update a project
     */
    public function update(User $user, Project $project): bool
    {
        // Cannot update closed projects
        if ($project->status?->name === 'closed') {
            return false;
        }

        // Super admin can always update
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI can update their project
        if ($project->pi_id === $user->id) {
            return true;
        }

        // Admin within same institution can update
        if ($user->isAdmin()) {
            $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
            return $pi instanceof User && $user->sharesInstitutionWith($pi);
        }

        return false;
    }

    /**
     * Determine if user can delete a project
     */
    public function delete(User $user, Project $project): bool
    {
        // Super admin can delete any project
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only draft or planning projects can be deleted
        if (!in_array($project->status?->name, ['draft', 'planning'])) {
            return false;
        }

        // PI can delete their draft/planning project
        return $project->pi_id === $user->id;
    }

    /**
     * Determine if user can submit a project for approval
     */
    public function submit(User $user, Project $project): bool
    {
        // Must be in draft status
        if ($project->status?->name !== 'draft') {
            return false;
        }

        // PI or admin can submit
        if ($project->pi_id === $user->id) {
            return true;
        }

        if ($user->isAdmin()) {
            $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
            return $pi instanceof User && $user->sharesInstitutionWith($pi);
        }

        return false;
    }

    /**
     * Determine if user can approve a project
     */
    public function approve(User $user, Project $project): bool
    {
        // Only research admins can approve
        if (!$user->hasRole('super_admin', 'research_admin')) {
            return false;
        }

        // Must be in planning status
        if ($project->status?->name !== 'planning') {
            return false;
        }

        // Must be from same institution
        $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
        return $pi instanceof User && $user->sharesInstitutionWith($pi);
    }

    /**
     * Determine if user can change project status
     */
    public function changeStatus(User $user, Project $project): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Research admin can change status
        if ($user->hasRole('research_admin')) {
            $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
            return $pi instanceof User && $user->sharesInstitutionWith($pi);
        }

        // PI can change some statuses
        if ($project->pi_id === $user->id) {
            // PI can suspend or reactivate their active project
            return in_array($project->status?->name, ['active', 'suspended']);
        }

        return false;
    }

    /**
     * Determine if user can complete a project
     */
    public function complete(User $user, Project $project): bool
    {
        // Must be in active status
        if ($project->status?->name !== 'active') {
            return false;
        }

        // PI or research admin can complete
        if ($project->pi_id === $user->id) {
            return true;
        }

        if ($user->hasRole('super_admin', 'research_admin')) {
            $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
            return $pi instanceof User && $user->sharesInstitutionWith($pi);
        }

        return false;
    }

    /**
     * Determine if user can manage project team (add/remove investigators)
     */
    public function manageTeam(User $user, Project $project): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI can manage their team
        if ($project->pi_id === $user->id) {
            return true;
        }

        // Admin within same institution can manage team
        if ($user->isAdmin()) {
            $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
            return $pi instanceof User && $user->sharesInstitutionWith($pi);
        }

        return false;
    }

    /**
     * Determine if user can attach files to project
     */
    public function attachFile(User $user, Project $project): bool
    {
        // Super admin can attach files
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Project members can attach files
        if ($project->isMember($user->id)) {
            return true;
        }

        // Admin within same institution can attach files
        if ($user->isAdmin()) {
            $pi = $project->relationLoaded('pi') ? $project->getRelation('pi') : $project->pi;
            return $pi instanceof User && $user->sharesInstitutionWith($pi);
        }

        return false;
    }

    /**
     * Determine if user can view project financial details
     */
    public function viewFinancials(User $user, Project $project): bool
    {
        // Super admin and research admin can view
        if ($user->hasRole('super_admin', 'research_admin')) {
            return true;
        }

        // PI can view financials
        if ($project->pi_id === $user->id) {
            return true;
        }

        return false;
    }
}
