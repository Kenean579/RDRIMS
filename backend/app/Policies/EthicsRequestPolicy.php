<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EthicsRequest;

class EthicsRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('ethics_officer');
    }

    public function view(User $user, EthicsRequest $ethicsRequest): bool
    {
        $submitter = $ethicsRequest->proposal?->submittedBy;

        if ($submitter && $submitter->id === $user->id) {
            return true;
        }

        if (! ($user->isAdmin() || $user->hasRole('ethics_officer'))) {
            return false;
        }

        return $submitter ? $user->sharesInstitutionWith($submitter) : false;
    }

    public function update(User $user, EthicsRequest $ethicsRequest): bool
    {
        if (! ($user->hasRole('ethics_officer') || $user->isAdmin())) {
            return false;
        }

        $submitter = $ethicsRequest->proposal?->submittedBy;

        return $submitter ? $user->sharesInstitutionWith($submitter) : false;
    }
}
