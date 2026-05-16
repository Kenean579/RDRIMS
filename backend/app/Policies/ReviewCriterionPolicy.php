<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ReviewCriterion;

class ReviewCriterionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReviewCriterion $reviewCriterion): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ReviewCriterion $reviewCriterion): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ReviewCriterion $reviewCriterion): bool
    {
        return $user->hasRole('super_admin');
    }
}
