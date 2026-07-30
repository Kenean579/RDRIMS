<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasTenantPermission
 *
 * Provides a reusable method to verify that a given model belongs to the same university
 * as the authenticated user. This is used across policies to enforce tenant isolation.
 */
trait HasTenantPermission
{
    /**
     * Determine if the given model belongs to the same university as the user.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    protected function sameUniversity(User $user, Model $model): bool
    {
        return $user->university_id !== null
            && $model->university_id !== null
            && $user->university_id === $model->university_id;
    }
}
?>
