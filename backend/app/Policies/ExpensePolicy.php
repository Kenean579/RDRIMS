<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->id === $expense->project->pi_id || $user->roles()->whereIn('name', ['admin', 'finance_officer'])->exists();
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'researcher'])->exists();
    }

    public function update(User $user, Expense $expense): bool
    {
        if ($user->roles()->whereIn('name', ['admin', 'finance_officer'])->exists()) return true;
        if ($user->id === $expense->project->pi_id && !$expense->approved_at) return true;
        return false;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'finance_officer'])->exists();
    }
}