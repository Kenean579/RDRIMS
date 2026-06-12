<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Collection;

class ReviewerSuggestionService
{
    /**
     * Suggest reviewers for a proposal based on keyword matching.
     * Logic: Exact match (1.0), Partial match (0.8), Fuzzy match (0.6)
     */
    public function suggest(Proposal $proposal, int $limit = 5): Collection
    {
        $proposalKeywords = array_filter(explode(',', $proposal->keywords));
        $proposalKeywords = array_map(fn($k) => strtolower(trim($k)), $proposalKeywords);

        if (empty($proposalKeywords)) {
            return User::whereHas('roles', fn($q) => $q->where('name', 'reviewer'))
                ->where('id', '!=', $proposal->submitted_by)
                ->limit($limit)
                ->get();
        }

        // Get all eligible reviewers (excluding submitter and investigators)
        $investigatorUserIds = $proposal->investigators()->whereNotNull('user_id')->pluck('user_id')->toArray();
        $eligibleReviewers = User::withCount('reviewedProposals')
            ->whereHas('roles', fn($q) => $q->where('name', 'reviewer'))
            ->where('id', '!=', $proposal->submitted_by)
            ->whereNotIn('id', $investigatorUserIds)
            ->with(['expertise', 'department'])
            ->get();

        $suggestions = $eligibleReviewers->map(function (User $reviewer) use ($proposalKeywords) {
            // Combine expertise tags and manually entered expertise_keywords
            $expertiseTags = $reviewer->expertise->pluck('name')->map(fn($n) => strtolower($n))->toArray();
            $manualKeywords = array_filter(explode(',', $reviewer->expertise_keywords ?? ''));
            $manualKeywords = array_map(fn($k) => strtolower(trim($k)), $manualKeywords);
            
            $reviewerExpertise = array_unique(array_merge($expertiseTags, $manualKeywords));
            
            $totalMatchScore = 0;
            $matchedKeywords = [];

            foreach ($proposalKeywords as $pk) {
                $bestKeywordScore = 0;
                
                foreach ($reviewerExpertise as $re) {
                    $score = 0;
                    if ($pk === $re) {
                        $score = 1.0;
                    } elseif (str_contains($pk, $re) || str_contains($re, $pk)) {
                        $score = 0.8;
                    } else {
                        $distance = levenshtein($pk, $re);
                        if ($distance <= 2) {
                            $score = 0.6;
                        }
                    }

                    if ($score > $bestKeywordScore) {
                        $bestKeywordScore = $score;
                    }
                }

                if ($bestKeywordScore > 0) {
                    $totalMatchScore += $bestKeywordScore;
                    $matchedKeywords[] = $pk;
                }
            }

            $matchPercentage = count($proposalKeywords) > 0 
                ? round(($totalMatchScore / count($proposalKeywords)) * 100) 
                : 0;

            return [
                'id' => $reviewer->id,
                'name' => $reviewer->name,
                'email' => $reviewer->email,
                'department' => $reviewer->department->name ?? 'N/A',
                'match_percentage' => $matchPercentage,
                'matched_keywords' => $matchedKeywords,
                'expertise' => $reviewerExpertise,
                // Add review count if needed (assuming history exists)
                'review_count' => $reviewer->reviewed_proposals_count,
            ];
        });

        return $suggestions->sortByDesc('match_percentage')->take($limit)->values();
    }
}
