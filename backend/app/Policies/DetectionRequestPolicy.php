<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DetectionRequest;

class DetectionRequestPolicy
{
    /**
     * Determine if user can view any detection requests
     */
    public function viewAny(User $user): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Researchers, admins, and detection officers can view
        return $user->hasAnyRole(['admin', 'detection_officer', 'researcher', 'faculty']);
    }

    /**
     * Determine if user can view a specific detection request
     */
    public function view(User $user, DetectionRequest $detectionRequest): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $requester = $detectionRequest->requestedBy;

        // Tenant isolation: verify shares institution
        if ($requester && !$user->sharesInstitutionWith($requester)) {
            return false;
        }

        // Requester can always view their own request
        if ($detectionRequest->requested_by === $user->id) {
            return true;
        }

        // Admin/officer from same institution can view
        if ($user->hasAnyRole(['admin', 'detection_officer'])) {
            return $requester ? true : false;
        }

        return false;
    }

    /**
     * Determine if user can create a detection request
     */
    public function create(User $user): bool
    {
        // Researchers, faculty, and admins can create requests
        return $user->hasAnyRole(['super_admin', 'admin', 'researcher', 'faculty', 'detection_officer']);
    }

    /**
     * Determine if user can complete a detection request
     */
    public function complete(User $user, DetectionRequest $detectionRequest): bool
    {
        // Super admin can complete any request
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only admins and detection officers can complete
        if (!$user->hasAnyRole(['admin', 'detection_officer'])) {
            return false;
        }

        // Tenant isolation: admin must share institution with requester
        $requester = $detectionRequest->requestedBy;
        return $requester ? $user->sharesInstitutionWith($requester) : false;
    }

    /**
     * Determine if user can retry a detection request
     */
    public function retry(User $user, DetectionRequest $detectionRequest): bool
    {
        // Super admin can retry any request
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only the requester can retry their own request
        if ($detectionRequest->requested_by !== $user->id) {
            return false;
        }

        // Request must be retryable
        return $detectionRequest->canRetry();
    }

    /**
     * Determine if user can review/mark a detection request as reviewed
     */
    public function markReviewed(User $user, DetectionRequest $detectionRequest): bool
    {
        // Super admin can review any request
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only admins and detection officers can review
        if (!$user->hasAnyRole(['admin', 'detection_officer'])) {
            return false;
        }

        // Tenant isolation check
        $requester = $detectionRequest->requestedBy;
        return $requester ? $user->sharesInstitutionWith($requester) : false;
    }

    /**
     * Determine if user can delete a detection request
     */
    public function delete(User $user, DetectionRequest $detectionRequest): bool
    {
        // Super admin can delete any request
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Cannot delete completed requests
        if ($detectionRequest->isCompleted()) {
            return false;
        }

        // Requester can delete their own draft request
        if ($detectionRequest->requested_by === $user->id && $detectionRequest->isPending()) {
            return true;
        }

        // Admin from same institution can delete non-completed requests
        if ($user->hasRole('admin')) {
            $requester = $detectionRequest->requestedBy;
            return $requester ? $user->sharesInstitutionWith($requester) : false;
        }

        return false;
    }

    /**
     * Determine if user can force-delete a detection request (soft delete removal)
     */
    public function forceDelete(User $user, DetectionRequest $detectionRequest): bool
    {
        // Only super admin can force delete
        return $user->hasRole('super_admin');
    }

    /**
     * Determine if user can restore a soft-deleted request
     */
    public function restore(User $user, DetectionRequest $detectionRequest): bool
    {
        // Super admin can restore
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin from same institution can restore
        if ($user->hasRole('admin')) {
            $requester = $detectionRequest->requestedBy;
            return $requester ? $user->sharesInstitutionWith($requester) : false;
        }

        return false;
    }
}
