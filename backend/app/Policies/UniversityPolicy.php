<?php

namespace App\Policies;

use App\Models\User;
use App\Models\University;

class UniversityPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, University $university): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('admin'); }
    public function update(User $user, University $university): bool { return $user->hasRole('admin'); }
    public function delete(User $user, University $university): bool { return $user->hasRole('admin'); }
}
