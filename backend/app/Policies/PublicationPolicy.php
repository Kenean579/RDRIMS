<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Publication;

class PublicationPolicy
{
    /**
     * Determine if the user can view any publications
     */
    public function viewAny(User $user): bool
    {
        return true; // List is scoped by hierarchical trait
    }

    /**
     * Determine if the user can view the publication
     */
    public function view(User $user, Publication $publication): bool
    {
        // Public publications are visible when the config allows it and the publication is published
        if (config('publications.public') && $publication->isPublished()) {
            return true;
        }
        // Existing checks follow
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }
        if ($publication->created_by === $user->id) {
            return true;
        }
        if (
            $user->hasPermission('manage_publications')
            && $publication->createdBy
            && $user->sharesInstitutionWith($publication->createdBy)
        ) {
            return true;
        }
        // Check if publication belongs to user's institution via project
        if ($publication->project) {
            if ($user->sharesInstitutionWith($publication->project->pi)) {
                return true;
            }
        }
        // Check if user is an author
        if ($publication->authors()->where('user_id', $user->id)->exists()) {
            return true;
        }
        return false;
    }

    /**
     * Determine if the user can create publications
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage_publications')
            || $user->hasAnyRole(['super_admin', 'admin', 'pi_role', 'researcher']);
    }

    /**
     * Determine if the user can update the publication
     */
    public function update(User $user, Publication $publication): bool
    {
        // Super admin can update all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Cannot update published publications (except super admin)
        if ($publication->isPublished()) {
            return false;
        }

        if ($publication->created_by === $user->id) {
            return true;
        }

        if (
            $user->hasPermission('manage_publications')
            && $publication->createdBy
            && $user->sharesInstitutionWith($publication->createdBy)
        ) {
            return true;
        }

        // Check tenant isolation via project
        if ($publication->project) {
            if (!$user->sharesInstitutionWith($publication->project->pi)) {
                return false;
            }
        }

        // Check if user is an author
        if ($publication->authors()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Admin from same institution can update
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the publication
     */
    public function delete(User $user, Publication $publication): bool
    {
        // Super admin can delete all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Cannot delete published publications
        if ($publication->isPublished()) {
            return false;
        }

        if ($publication->created_by === $user->id) {
            return true;
        }

        if (
            $user->hasPermission('manage_publications')
            && $publication->createdBy
            && $user->sharesInstitutionWith($publication->createdBy)
        ) {
            return true;
        }

        // Check tenant isolation
        if ($publication->project) {
            if (!$user->sharesInstitutionWith($publication->project->pi)) {
                return false;
            }
        }

        // Only admin or first author can delete
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check if user is first author
        $firstAuthor = $publication->authors()->where('author_order', 1)->first();
        if ($firstAuthor && $firstAuthor->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can submit the publication
     */
    public function submit(User $user, Publication $publication): bool
    {
        // Check if user has permission to update (author or admin)
        if (!$this->update($user, $publication)) {
            return false;
        }

        // Only check status here - business validation (internal author) is in service layer
        return $publication->isDraft();
    }

    /**
     * Determine if the user can verify the publication
     */
    public function verify(User $user, Publication $publication): bool
    {
        // Institutional publication approvers.
        if (!$user->hasAnyRole([
            'research_admin',
            'campus_admin',
            'faculty_admin',
            'department_head',
            'director',
        ])) {
            return false;
        }

        if ($publication->createdBy
            && !$user->sharesInstitutionWith($publication->createdBy)) {
            return false;
        }

        // Check tenant isolation
        if ($publication->project) {
            if (!$user->sharesInstitutionWith($publication->project->pi)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the user can approve the publication
     */
    public function approve(User $user, Publication $publication): bool
    {
        return $this->verify($user, $publication);
    }

    /**
     * Determine if the user can publish the publication
     */
    public function publish(User $user, Publication $publication): bool
    {
        return $this->verify($user, $publication) && $publication->canPublish();
    }

    /**
     * Determine if the user can manage authors
     */
    public function manageAuthors(User $user, Publication $publication): bool
    {
        return $this->update($user, $publication);
    }
}
