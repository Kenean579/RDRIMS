<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Campus;

class CampusPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Campus $campus): bool { return true; }
    public function create(User $user): bool { return $user->hasRole('admin'); }
    public function update(User $user, Campus $campus): bool { return $user->hasRole('admin'); }
    public function delete(User $user, Campus $campus): bool { return $user->hasRole('admin'); }
}
