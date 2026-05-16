<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Expertise;

class ExpertisePolicy
{
    public function viewAny(User $user): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('admin'); }
    public function update(User $user, Expertise $expertise): bool { return $user->hasRole('admin'); }
    public function delete(User $user, Expertise $expertise): bool { return $user->hasRole('admin'); }
}
