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
        if (! ($user->isAdmin() || $user->hasRole('finance_officer'))) {
            return false;
        }

        $submitter = $financeCheck->proposal?->submittedBy;

        return $submitter ? $user->sharesInstitutionWith($submitter) : false;
    }

    public function update(User $user, FinanceCheck $financeCheck): bool
    {
        if (! ($user->hasRole('finance_officer') || $user->isAdmin())) {
            return false;
        }

        $submitter = $financeCheck->proposal?->submittedBy;

        return $submitter ? $user->sharesInstitutionWith($submitter) : false;
    }
}
