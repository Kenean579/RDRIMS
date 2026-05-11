<?php

namespace App\Services;

use App\Models\CommunityProblem;
use App\Models\CommunityProblemStatus;

class CommunityProblemService
{
    public function claim(CommunityProblem $problem, int $userId): void
    {
        if ($problem->status->name !== 'open') {
            abort(422, 'Only open problems can be claimed.');
        }
        if ($problem->claimed_by) {
            abort(422, 'Problem is already claimed.');
        }

        $problem->update([
            'claimed_by' => $userId,
            'claimed_at' => now(),
            'status_id'  => CommunityProblemStatus::where('name', 'claimed')->first()->id,
        ]);
    }

    public function complete(CommunityProblem $problem): void
    {
        if ($problem->status->name !== 'claimed') {
            abort(422, 'Only claimed problems can be completed.');
        }

        $problem->update([
            'completed_at' => now(),
            'status_id'    => CommunityProblemStatus::where('name', 'completed')->first()->id,
        ]);
    }

    public function addFeedback(CommunityProblem $problem, string $feedback, int $rating): void
    {
        $problem->update([
            'feedback' => $feedback,
            'rating'   => $rating,
        ]);
    }
}