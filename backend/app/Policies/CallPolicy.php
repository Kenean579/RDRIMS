<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CallPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any calls.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the call.
     */
    public function view(?User $user, Call $call): bool
    {
        if (!$user) {
            return true; // Allow unauthenticated access to call details for public view
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Creator can always view their own call
        if ($call->created_by && (int) $call->created_by === (int) $user->id) {
            return true;
        }

        // Enforce that the call belongs to the same university as the user
        $userUniId = $user->resolvedUniversityId();
        return $call->university_id === null || (int) $call->university_id === (int) $userUniId;
    }

    /**
     * Determine whether the user can create calls.
     */
    public function create(User $user): bool
    {
        // Roles allowed to create calls.
        return $user->hasAnyRole([
            'super_admin',
            'research_admin',
            'director',
            'campus_admin',
            'faculty_admin',
            'department_head',
        ]);
    }

    /**
     * Determine whether the user can update the call.
     */
    public function update(User $user, Call $call): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Must belong to the same university
        $userUniId = $user->resolvedUniversityId();
        if ($call->university_id !== null && (int)$call->university_id !== (int)$userUniId) {
            return false;
        }

        if ($user->hasRole('research_admin')) {
            return true;
        }

        if ($user->hasRole('campus_admin')) {
            $userCampus = $user->campus_id ?: $user->department?->faculty?->campus_id;
            return $userCampus && (int)$call->campus_id === (int)$userCampus;
        }

        if ($user->hasRole('faculty_admin')) {
            $userFaculty = $user->faculty_id ?: $user->department?->faculty_id;
            return $userFaculty && (int)$call->faculty_id === (int)$userFaculty;
        }

        if ($user->hasRole('department_head')) {
            return $user->department_id && (int)$call->department_id === (int)$user->department_id;
        }

        if ($user->hasRole('director')) {
            $centerIds = $user->researchCenters()->pluck('research_centers.id')->toArray();
            return $call->research_center_id && in_array((int)$call->research_center_id, $centerIds);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the call.
     */
    public function delete(User $user, Call $call): bool
    {
        return $this->update($user, $call);
    }

    /**
     * Determine whether the user can restore a soft‑deleted call.
     */
    public function restore(User $user, Call $call): bool
    {
        return $this->update($user, $call);
    }

    /**
     * Determine whether the user can permanently delete the call.
     */
    public function forceDelete(User $user, Call $call): bool
    {
        // Only Super Admin can force delete.
        return $user->hasRole('super_admin');
    }
}
