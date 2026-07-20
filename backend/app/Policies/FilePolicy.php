<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    public function view(User $user, File $file): bool
    {
        if ($file->uploaded_by === $user->id) {
            return true;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        $uploader = $file->relationLoaded('uploader')
            ? $file->getRelation('uploader')
            : $file->uploader;

        if ($file->is_public) {
            return $uploader instanceof User && $user->sharesInstitutionWith($uploader);
        }

        return false;
    }

    public function download(User $user, File $file): bool
    {
        return $this->view($user, $file);
    }

    public function delete(User $user, File $file): bool
    {
        if ($file->uploaded_by === $user->id) {
            return true;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        $uploader = $file->relationLoaded('uploader')
            ? $file->getRelation('uploader')
            : $file->uploader;

        return $uploader instanceof User && $user->isAdmin() && $user->sharesInstitutionWith($uploader);
    }
}
