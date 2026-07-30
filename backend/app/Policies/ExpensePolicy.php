<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Determine if user can view expense
     */
    public function view(User $user, Expense $expense): bool
    {
        $project = $expense->project;
        
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI can view all expenses
        if ($project->pi_id === $user->id) {
            return true;
        }

        // Research admin within same institution can view
        if ($user->hasRole('research_admin') && $project->pi) {
            return $user->sharesInstitutionWith($project->pi);
        }

        return false;
    }

    /**
     * Determine if user can create expenses
     */
    public function create(User $user, $project): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Project members can create expenses
        if ($project->isMember($user->id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can update expense
     */
    public function update(User $user, Expense $expense): bool
    {
        // Cannot update approved expenses
        if ($expense->approved_by !== null) {
            return false;
        }

        $project = $expense->project;
        
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // PI can update pending expenses
        if ($project->pi_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can delete expense
     */
    public function delete(User $user, Expense $expense): bool
    {
        // Cannot delete approved expenses
        if ($expense->approved_by !== null) {
            return false;
        }

        $project = $expense->project;
        
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only PI can delete expenses
        return $project->pi_id === $user->id;
    }

    /**
     * Determine if user can approve expenses
     */
    public function approve(User $user, Expense $expense): bool
    {
        $project = $expense->project;
        
        // Only research admins can approve
        if (!$user->hasRole('super_admin', 'research_admin')) {
            return false;
        }

        // Must be from same institution
        if ($project->pi) {
            return $user->sharesInstitutionWith($project->pi);
        }

        return false;
    }
}
