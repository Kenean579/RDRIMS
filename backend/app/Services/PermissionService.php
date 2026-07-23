<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Get all effective permissions for a user.
     */
    public function getEffectivePermissions(User $user): Collection
    {
        $ids = $user->getEffectivePermissionIds();
        if (empty($ids)) return collect([]);
        
        return Permission::whereIn('id', $ids)->get();
    }

    /**
     * Clear the permission cache for a specific user.
     */
    public function clearUserCache(User $user): void
    {
        $universityId = $user->resolvedUniversityId() ?: 0;

        Cache::forget("user_{$user->id}_uni_{$universityId}_effective_permissions_v4");
    }

    /**
     * Clear the permission cache for all users belonging to a university.
     * This should be called when institutional overrides change.
     */
    public function clearCacheForUniversity(int $universityId): void
    {
        // Find all users associated with this university
        $users = User::where('university_id', $universityId)
            ->orWhereHas('department.faculty.campus', function ($q) use ($universityId) {
                $q->where('university_id', $universityId);
            })->get();

        foreach ($users as $user) {
            $this->clearUserCache($user);
        }
    }
}
