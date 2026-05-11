<?php
// app/Services/ReviewerSuggestionService.php

namespace App\Services;

use App\Models\Proposal;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;

class ReviewerSuggestionService
{
    /**
     * Suggest top N reviewers based on keyword overlap, excluding PI and existing reviewers.
     */
    public function suggest(Proposal $proposal, int $limit = 5): Collection
    {
        $reviewerRoleId = Role::where('name', 'reviewer')->value('id');

        $potential = User::whereHas('roles', function ($q) use ($reviewerRoleId) {
                $q->where('role_id', $reviewerRoleId);
            })
            ->with('expertise')
            ->where('is_active', true)
            ->get();

        // Exclude proposal owner and already assigned reviewers
        $assignedIds = $proposal->reviewers()->pluck('users.id')->toArray();
        $potential = $potential->filter(function ($user) use ($proposal, $assignedIds) {
            return $user->id !== $proposal->submitted_by && !in_array($user->id, $assignedIds);
        });

        $keywords = array_map('trim', explode(',', $proposal->keywords ?? ''));
        $keywords = array_filter($keywords);

        $scored = $potential->map(function ($user) use ($keywords) {
            $expertise = $user->expertise->pluck('name')->toArray();
            $matchCount = count(array_intersect($keywords, $expertise));
            $pastReviews = $user->reviewerProposals()->count();
            $totalScore = $matchCount + ($pastReviews * 0.1); // slight weight for experience
            return (object) ['reviewer' => $user, 'score' => $totalScore];
        });

        return $scored->sortByDesc('score')
            ->take($limit)
            ->values()
            ->map(fn($item) => $item->reviewer);
    }
}
