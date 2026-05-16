<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DetectionRequest;

class DetectionRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, DetectionRequest $detectionRequest): bool
    {
        return $user->isAdmin() || $detectionRequest->submitted_by === $user->id;
    }

    public function update(User $user, DetectionRequest $detectionRequest): bool
    {
        return $user->isAdmin();
    }
}
