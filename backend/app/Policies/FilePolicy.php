<?php

namespace App\Policies;

use App\Models\User;
use App\Models\File;

class FilePolicy
{
    public function view(User $user, File $file): bool
    {
        return $file->is_public || $file->uploaded_by === $user->id || $user->isAdmin();
    }

    public function download(User $user, File $file): bool
    {
        return $this->view($user, $file);
    }

    public function delete(User $user, File $file): bool
    {
        return $file->uploaded_by === $user->id || $user->hasRole('super_admin');
    }
}
