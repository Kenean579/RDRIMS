<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expense;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->isAdmin() || $expense->project->pi_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->isAdmin() || $expense->project->pi_id === $user->id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->isAdmin();
    }
}