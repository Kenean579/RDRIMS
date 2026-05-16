<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Collection;

class ReviewerSuggestionService
{
    public function suggest(Proposal $proposal, int $limit = 5): Collection
    {
        $keywords = array_filter(explode(',', $proposal->keywords));
        $keywords = array_map('trim', $keywords);

        if (empty($keywords)) {
            return User::whereHas('roles', fn($q) => $q->where('name', 'reviewer'))
                ->where('id', '!=', $proposal->submitted_by)
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        return User::whereHas('roles', fn($q) => $q->where('name', 'reviewer'))
            ->where('id', '!=', $proposal->submitted_by)
            ->where(function ($query) use ($keywords) {
                $query->whereHas('expertise', function ($q) use ($keywords) {
                    $q->whereIn('name', $keywords);
                });
            })
            ->limit($limit)
            ->get();
    }
}
