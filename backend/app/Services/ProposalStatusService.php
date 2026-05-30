<?php
// app/Services/ProposalStatusService.php

namespace App\Services;

use App\Models\Proposal;
use App\Models\ProposalStatus;

class ProposalStatusService
{
    /**
     * Valid transitions: current status => allowed next status names.
     * These names must exist in `proposal_statuses` lookup table.
     */
    protected array $transitions = [
        'draft'             => ['submitted', 'withdrawn'],
        'submitted'         => ['under_review', 'withdrawn'],
        'under_review'      => ['revision_requested', 'accepted', 'rejected'],
        'revision_requested'=> ['submitted', 'withdrawn'],
        'accepted'          => [],
        'rejected'          => [],
        'withdrawn'         => [],
    ];

    /**
     * Check if a proposal can move to the given status.
     */
    public function canTransition(Proposal $proposal, string $newStatusName): bool
    {
        $current = $proposal->status->name;
        return in_array($newStatusName, $this->transitions[$current] ?? []);
    }

    /**
     * Perform the status transition with optional comment.
     */
    public function transition(Proposal $proposal, string $newStatusName, ?string $comment = null): bool
    {
        if (!$this->canTransition($proposal, $newStatusName)) {
            return false;
        }

        $newStatusId = ProposalStatus::where('name', $newStatusName)->value('id');
        $proposal->update([
            'status_id'            => $newStatusId,
            'status_change_comment' => $comment,
        ]);

        return true;
    }

    /**
     * Get all possible next statuses as a collection of objects.
     */
    public function getPossibleNextStatuses(Proposal $proposal): \Illuminate\Support\Collection
    {
        $current = $proposal->status->name;
        $allowed = $this->transitions[$current] ?? [];
        return ProposalStatus::whereIn('name', $allowed)->get();
    }
}
