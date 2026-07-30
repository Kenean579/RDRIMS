<?php

namespace App\Policies;

use App\Models\Funding;
use App\Models\User;

class FundingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    public function view(User $user, Funding $funding): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Tenant isolation: User must share institution with funding creator
        if ($user->id === $funding->created_by) {
            return true;
        }

        // Admin within same institution can view
        $userUniversityId = $user->university_id ?? $user->getUniversityId();
        if ($user->isAdmin() && $userUniversityId === $funding->university_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Only admins and above can create fundings
        return $user->isAdmin() || $user->hasRole('super_admin');
    }

    public function update(User $user, Funding $funding): bool
    {
        // Check if in draft status
        $isDraft = $funding->status?->name === 'draft';

        // Super admin can always update
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Creator can update draft funding
        if ($funding->created_by === $user->id && $isDraft) {
            return true;
        }

        // Institution admin can update draft funding
        $userUniversityId = $user->university_id ?? $user->getUniversityId();
        if ($user->isAdmin() && $userUniversityId === $funding->university_id && $isDraft) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Funding $funding): bool
    {
        // Check if in draft status
        $isDraft = $funding->status?->name === 'draft';

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $funding->created_by === $user->id && $isDraft;
    }

    public function submit(User $user, Funding $funding): bool
    {
        $isDraft = $funding->status?->name === 'draft';
        return $funding->created_by === $user->id && $isDraft;
    }

    public function approve(User $user, Funding $funding): bool
    {
        // Only research admins can approve fundings
        $userUniversityId = $user->university_id ?? $user->getUniversityId();
        return $user->hasRole('super_admin', 'research_admin') && 
               $userUniversityId === $funding->university_id;
    }

    public function reject(User $user, Funding $funding): bool
    {
        $userUniversityId = $user->university_id ?? $user->getUniversityId();
        return $user->hasRole('super_admin', 'research_admin') && 
               $userUniversityId === $funding->university_id;
    }
}
