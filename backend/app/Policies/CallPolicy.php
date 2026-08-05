<?php

namespace App\Policies;

use App\Models\Call;
// use App\Traits\HasTenantPermission;
use App\Models\User;

/**
 * CallPolicy
 *
 * Permission-based authorization for Call resources.
 * Follows the production-ready pattern from Campus/Faculty/Department/Research Center modules.
 *
 * Key Features:
 * - Dynamic permission system (call.*)
 * - Strict tenant isolation
 * - Public access preserved for portal
 * - Super admin explicitly denied (tenant resources only)
 */
class CallPolicy
{
    // use HasTenantPermission;

    /**
     * Determine whether the user can view any calls.
     *
     * Public access preserved for portal.
     * Authenticated users require call.viewAny permission.
     */
    public function viewAny(?User $user): bool
    {
        // Allow unauthenticated access for public portal
        if (!$user) {
            return true;
        }

        // Deny super_admin (tenant resources only)
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Permission-based authorization
        return $user->hasPermission('call.viewAny');
    }

    /**
     * Determine whether the user can view the call.
     *
     * Public calls: Accessible to unauthenticated users if published
     * Private calls: Require authentication + permission + tenant ownership
     */
    public function view(?User $user, Call $call): bool
{
    /*
    |--------------------------------------------------------------------------
    | Public published calls
    |--------------------------------------------------------------------------
    */

    if (
        $call->is_public &&
        $call->published_at !== null &&
        $call->published_at <= now()
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Guests cannot access private calls
    |--------------------------------------------------------------------------
    */

    if (!$user) {
        return false;
    }

    if ($user->hasRole('super_admin')) {
        return false;
    }

    return $user->hasPermission('call.view')
        && $this->sameUniversity($user, $call);
}

    /**
     * Determine whether the user can create calls.
     *
     * Requires call.create permission.
     * Super admin explicitly denied.
     */
    public function create(User $user): bool
    {
        // Deny super_admin (tenant resources only)
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Permission-based authorization
        return $user->hasPermission('call.create');
    }

    /**
     * Determine whether the user can update the call.
     *
     * Requires call.update permission + tenant ownership.
     * Additional restrictions enforced by CallService (status-based editing).
     */
    public function update(User $user, Call $call): bool
    {
        // Deny super_admin (tenant resources only)
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Check permission + tenant ownership
        return $this->sameUniversity($user, $call)
            && $user->hasPermission('call.update');
    }

    /**
     * Determine whether the user can delete the call.
     *
     * Requires call.delete permission + tenant ownership.
     * Additional business rules enforced in controller (proposal check).
     */
    public function delete(User $user, Call $call): bool
    {
        // Deny super_admin (tenant resources only)
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Check permission + tenant ownership
        return $this->sameUniversity($user, $call)
            && $user->hasPermission('call.delete');
    }

    /**
     * Determine whether the user can restore a soft-deleted call.
     */
    public function restore(User $user, Call $call): bool
    {
        // Same rules as update
        return $this->update($user, $call);
    }

    /**
     * Determine whether the user can permanently delete the call.
     *
     * Force delete denied for all users (including super_admin).
     * Use soft delete to preserve historical data integrity.
     */
    public function forceDelete(User $user, Call $call): bool
    {
        // Deny all force deletes to protect data integrity
        // Proposals depend on call history
        return false;
    }

    /**
     * Verify tenant ownership.
     *
     * Ensures the call belongs to the same university as the user.
     *
     * @param User $user
     * @param Call $call
     * @return bool True if user owns the call's university
     */

}

