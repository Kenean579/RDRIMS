<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Output;

class OutputPolicy
{
    /**
     * Determine if user can view any outputs
     */
    public function viewAny(User $user): bool
    {
        return true; // List is scoped by hierarchical trait
    }

    /**
     * Determine if user can view a specific output
     */
    public function view(User $user, Output $output): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check if user is a participant
        if ($output->participants()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // Check if user shares institution with participants (via hierarchical scope)
        if ($output->participants()->hierarchical($user, 'id')->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can create an output
     */
    public function create(User $user): bool
    {
        // Allow researchers and admins to create outputs
        return $user->hasAnyRole(['super_admin', 'admin', 'researcher', 'faculty', 'pi_role', 'student']);
    }

    /**
     * Determine if user can update an output
     */
    public function update(User $user, Output $output): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Cannot update verified or published outputs
        if ($output->isVerified() || $output->isPublished()) {
            return false;
        }

        // Check tenant isolation via project
        if ($output->project) {
            if (!$user->sharesInstitutionWith($output->project->pi)) {
                return false;
            }
        }

        // Check if user is a participant
        if ($output->participants()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // Admin from same institution can update
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete an output
     */
    public function delete(User $user, Output $output): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Cannot delete verified or published outputs
        if ($output->isVerified() || $output->isPublished()) {
            return false;
        }

        // Check tenant isolation
        if ($output->project) {
            if (!$user->sharesInstitutionWith($output->project->pi)) {
                return false;
            }
        }

        // Check if user is a participant
        if ($output->participants()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // Only admin can delete
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can submit an output
     */
    public function submit(User $user, Output $output): bool
    {
        // Super admin can always submit
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin from same institution can submit
        if ($user->hasRole('admin')) {
            // Check tenant isolation
            if ($output->project) {
                if ($user->sharesInstitutionWith($output->project->pi)) {
                    return true;
                }
            }
            // Check via participants if no project
            if (!$output->project && $output->participants()->hierarchical($user, 'id')->exists()) {
                return true;
            }
        }

        // Must have update permission first for non-admins
        if (!$this->update($user, $output)) {
            return false;
        }

        // Only allow if in draft status (business logic in service layer validates)
        return $output->isDraft();
    }

    /**
     * Determine if user can verify an output (admin only)
     */
    public function verify(User $user, Output $output): bool
    {
        // Only admin or super admin can verify
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            return false;
        }

        // Check tenant isolation
        if ($output->project) {
            if (!$user->sharesInstitutionWith($output->project->pi)) {
                return false;
            }
        }

        // Check tenant isolation via participants
        if (!$output->project && $output->participants()->count() > 0) {
            if (!$output->participants()->hierarchical($user, 'id')->exists()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if user can approve an output
     */
    public function approve(User $user, Output $output): bool
    {
        return $this->verify($user, $output);
    }

    /**
     * Determine if user can reject an output
     */
    public function reject(User $user, Output $output): bool
    {
        return $this->verify($user, $output);
    }

    /**
     * Determine if user can publish an output
     */
    public function publish(User $user, Output $output): bool
    {
        return $this->verify($user, $output) && $output->canPublish();
    }

    /**
     * Determine if user can change status of an output
     */
    public function changeStatus(User $user, Output $output): bool
    {
        // Check update permission
        if (!$this->update($user, $output)) {
            return false;
        }

        // Additional check for admin-level status changes
        if ($output->isSubmitted()) {
            return $user->hasRole('admin') || $user->hasRole('super_admin');
        }

        return true;
    }
}
