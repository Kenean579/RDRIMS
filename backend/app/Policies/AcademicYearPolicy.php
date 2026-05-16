<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AcademicYear;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole('super_admin');
    }
}
