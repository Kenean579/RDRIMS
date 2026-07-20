<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Adds a global scope that automatically restricts queries to the authenticated user's university.
 */
trait BelongsToUniversity
{
    /**
     * Boot the trait and attach the global scope.
     */
    protected static function bootBelongsToUniversity()
    {
        if (! is_subclass_of(static::class, \Illuminate\Database\Eloquent\Model::class)) {
            return;
        }

        forward_static_call([
            static::class,
            'addGlobalScope',
        ], 'university', function (Builder $builder) {
            $tenant = app()->bound('tenant') ? app('tenant') : null;
            $tenantId = $tenant?->id ?? null;

            if ($tenantId) {
                $builder->where('university_id', $tenantId);
                return;
            }

            $user = Auth::user();
            if ($user && $user->resolvedUniversityId()) {
                $builder->where('university_id', $user->resolvedUniversityId());
            }
        });
    }
}
