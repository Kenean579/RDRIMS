<?php

namespace App\Policies;

use App\Models\File;
use App\Models\Proposal;
use App\Models\User;

class FilePolicy
{
    public function view(User $user, File $file): bool
    {
        if ($file->is_public || $file->uploaded_by === $user->id || $user->isAdmin()) {
            return true;
        }

        return Proposal::where('file_id', $file->id)
            ->whereHas('reviewers', fn ($q) => $q->where('reviewer_id', $user->id))
            ->exists();
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
