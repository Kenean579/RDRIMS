<?php

namespace App\Policies;

use App\Models\FundingExpense;
use App\Models\User;

class FundingExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    public function view(User $user, FundingExpense $expense): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Submitter can view
        if ($user->id === $expense->submitted_by) {
            return true;
        }

        // Admin in same institution can view
        $userUniversityId = $user->university_id ?? $user->getUniversityId();
        if ($user->isAdmin() && 
            $userUniversityId === $expense->funding->university_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->id !== null;
    }

    public function update(User $user, FundingExpense $expense): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only pending expenses can be updated
        if ($expense->status !== 'pending') {
            return false;
        }

        // Submitter can update pending expense
        return $user->id === $expense->submitted_by;
    }

    public function delete(User $user, FundingExpense $expense): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only pending expenses can be deleted
        if ($expense->status !== 'pending') {
            return false;
        }

        return $user->id === $expense->submitted_by;
    }

    public function approve(User $user, FundingExpense $expense): bool
    {
        // Only admins can approve
        $userUniversityId = $user->university_id ?? $user->getUniversityId();
        return $user->hasRole('super_admin', 'research_admin') && 
               $userUniversityId === $expense->funding->university_id;
    }

    public function reject(User $user, FundingExpense $expense): bool
    {
        // Only admins can reject
        $userUniversityId = $user->university_id ?? $user->getUniversityId();
        return $user->hasRole('super_admin', 'research_admin') && 
               $userUniversityId === $expense->funding->university_id;
    }
}
