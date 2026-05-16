<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Faculty;

class FacultyPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Faculty $faculty): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('admin'); }
    public function update(User $user, Faculty $faculty): bool { return $user->hasRole('admin'); }
    public function delete(User $user, Faculty $faculty): bool { return $user->hasRole('admin'); }
}
