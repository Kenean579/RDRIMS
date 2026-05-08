<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'research_admin', 'department_head'])->exists();
    }

    public function view(User $user, Report $report): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->roles()->whereIn('name', ['admin', 'research_admin'])->exists();
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->roles()->where('name', 'admin')->exists();
    }
}