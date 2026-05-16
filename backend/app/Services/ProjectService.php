<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;

class ProjectService
{
    public function createFromProposal(Proposal $proposal, User $creator): Project
    {
        if ($proposal->status_id !== 5) { // approved
            abort(422, 'Only approved proposals can be converted to projects.');
        }

        if ($proposal->project()->exists()) {
            abort(422, 'A project already exists for this proposal.');
        }

        return $proposal->project()->create([
            'title' => $proposal->title,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'total_budget' => $proposal->budget,
            'budget_allocation' => $proposal->budget_allocation,
            'status_id' => 1, // active
            'pi_id' => $proposal->submitted_by,
            'academic_year_id' => $proposal->academic_year_id,
        ]);
    }
}