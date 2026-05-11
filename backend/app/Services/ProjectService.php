<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\Project;
use App\Models\ProjectStatus;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function createFromProposal(Proposal $proposal, int $piId): Project
    {
        if ($proposal->status->name !== 'approved') {
            abort(422, 'Only approved proposals can be converted to a project.');
        }

        return DB::transaction(function () use ($proposal, $piId) {
            return Project::create([
                'proposal_id'       => $proposal->id,
                'title'             => $proposal->title,
                'start_date'        => now()->toDateString(),
                'end_date'          => now()->addYear()->toDateString(),
                'total_budget'      => $proposal->budget,
                'budget_allocation' => $proposal->budget_allocation,
                'status_id'         => ProjectStatus::where('name', 'active')->first()->id,
                'pi_id'             => $piId,
                'academic_year_id'  => $proposal->academic_year_id,
            ]);
        });
    }
}