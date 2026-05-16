<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FinanceCheck;

class FinanceCheckPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('finance_officer');
    }

    public function view(User $user, FinanceCheck $financeCheck): bool
    {
        return $user->isAdmin() || $user->hasRole('finance_officer');
    }

    public function update(User $user, FinanceCheck $financeCheck): bool
    {
        return $user->hasRole('finance_officer') || $user->isAdmin();
    }
}
