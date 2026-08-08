<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\ProjectInvestigator;
use App\Models\ProjectStatus;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Create a new project
     */
    public function create(array $data, int $userId): Project
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;

            // Get draft status
            $draftStatus = ProjectStatus::where('name', 'draft')->first();
            if (!$draftStatus) {
                throw new \Exception('Draft status not found. Please seed project statuses.');
            }

            // Temporarily unguard to set status_id
            Project::unguard();
            $data['status_id'] = $draftStatus->id;
            $project = Project::create($data);
            Project::reguard();

            // Log creation
            $this->logHistory(
                $project,
                'created',
                $userId,
                "Project '{$project->title}' created"
            );

            return $project->fresh();
        });
    }

    /**
     * Update a project
     */
    public function update(Project $project, array $data, int $userId): Project
    {
        return DB::transaction(function () use ($project, $data, $userId) {
            $oldData = $project->only(['title', 'description', 'start_date', 'end_date', 'total_budget']);
            $data['updated_by'] = $userId;

            $project->update($data);

            // Log update
            $this->logHistory(
                $project,
                'updated',
                $userId,
                "Project '{$project->title}' updated",
                ['old' => $oldData, 'new' => $data]
            );

            return $project;
        });
    }

    /**
     * Submit project for approval
     */
    public function submit(Project $project, int $userId): Project
    {
        return DB::transaction(function () use ($project, $userId) {
            // Validation: must be in draft status
            if ($project->status?->name !== 'draft') {
                throw new \InvalidArgumentException('Only draft projects can be submitted');
            }

            // Validation: must have at least one milestone
            if ($project->milestones()->count() === 0) {
                throw new \InvalidArgumentException('Project must have at least one milestone before submission');
            }

            $planningStatus = ProjectStatus::where('name', 'planning')->first();

            // Unguard to update status_id
            Project::unguard();
            $project->update([
                'status_id' => $planningStatus->id,
                'updated_by' => $userId,
            ]);
            Project::reguard();

            $this->logHistory(
                $project,
                'submitted',
                $userId,
                "Project '{$project->title}' submitted for approval"
            );

            return $project->fresh();
        });
    }

    /**
     * Approve a project
     */
    public function approve(Project $project, int $userId, ?string $comments = null): Project
    {
        return DB::transaction(function () use ($project, $userId, $comments) {
            // Validation: must be in planning status
            if ($project->status?->name !== 'planning') {
                throw new \InvalidArgumentException('Only planning projects can be approved');
            }

            $activeStatus = ProjectStatus::where('name', 'active')->first();

            // Unguard to update status_id
            Project::unguard();
            $project->update([
                'status_id' => $activeStatus->id,
                'updated_by' => $userId,
            ]);
            Project::reguard();

            $this->logHistory(
                $project,
                'approved',
                $userId,
                "Project '{$project->title}' approved" . ($comments ? ": $comments" : '')
            );

            return $project->fresh();
        });
    }

    /**
     * Reject a project
     */
    public function reject(Project $project, int $userId, string $reason): Project
    {
        return DB::transaction(function () use ($project, $userId, $reason) {
            // Validation: must be in planning status
            if ($project->status?->name !== 'planning') {
                throw new \InvalidArgumentException('Only planning projects can be rejected');
            }

            $draftStatus = ProjectStatus::where('name', 'draft')->first();

            // Unguard to update status_id
            Project::unguard();
            $project->update([
                'status_id' => $draftStatus->id,
                'updated_by' => $userId,
            ]);
            Project::reguard();

            $this->logHistory(
                $project,
                'rejected',
                $userId,
                "Project '{$project->title}' rejected: $reason"
            );

            return $project->fresh();
        });
    }

    /**
     * Suspend a project
     */
    public function suspend(Project $project, int $userId, string $reason): Project
    {
        return DB::transaction(function () use ($project, $userId, $reason) {
            // Validation: must be in active status
            if ($project->status?->name !== 'active') {
                throw new \InvalidArgumentException('Only active projects can be suspended');
            }

            $suspendedStatus = ProjectStatus::where('name', 'suspended')->first();

            // Unguard to update status_id
            Project::unguard();
            $project->update([
                'status_id' => $suspendedStatus->id,
                'updated_by' => $userId,
            ]);
            Project::reguard();

            $this->logHistory(
                $project,
                'suspended',
                $userId,
                "Project '{$project->title}' suspended: $reason"
            );

            return $project->fresh();
        });
    }

    /**
     * Reactivate a suspended project
     */
    public function reactivate(Project $project, int $userId): Project
    {
        return DB::transaction(function () use ($project, $userId) {
            // Validation: must be in suspended status
            if ($project->status?->name !== 'suspended') {
                throw new \InvalidArgumentException('Only suspended projects can be reactivated');
            }

            $activeStatus = ProjectStatus::where('name', 'active')->first();

            // Unguard to update status_id
            Project::unguard();
            $project->update([
                'status_id' => $activeStatus->id,
                'updated_by' => $userId,
            ]);
            Project::reguard();

            $this->logHistory(
                $project,
                'reactivated',
                $userId,
                "Project '{$project->title}' reactivated"
            );

            return $project->fresh();
        });
    }

    /**
     * Complete a project
     */
    public function complete(Project $project, int $userId): Project
    {
        return DB::transaction(function () use ($project, $userId) {
            // Validation: must be in active status
            if ($project->status?->name !== 'active') {
                throw new \InvalidArgumentException('Only active projects can be completed');
            }

            // Validation: all milestones must be completed
            if (!$project->canComplete()) {
                throw new \InvalidArgumentException('All milestones must be completed before project completion');
            }

            $completedStatus = ProjectStatus::where('name', 'completed')->first();

            // Unguard to update status_id
            Project::unguard();
            $project->update([
                'status_id' => $completedStatus->id,
                'updated_by' => $userId,
            ]);
            Project::reguard();

            $this->logHistory(
                $project,
                'completed',
                $userId,
                "Project '{$project->title}' marked as completed"
            );

            return $project->fresh();
        });
    }

    /**
     * Close a project (archive)
     */
    public function close(Project $project, int $userId): Project
    {
        return DB::transaction(function () use ($project, $userId) {
            // Validation: must be completed
            if ($project->status?->name !== 'completed') {
                throw new \InvalidArgumentException('Only completed projects can be closed');
            }

            $closedStatus = ProjectStatus::where('name', 'closed')->first();
            $project->update([
                'status_id' => $closedStatus->id,
                'updated_by' => $userId,
            ]);

            $this->logHistory(
                $project,
                'closed',
                $userId,
                "Project '{$project->title}' closed and archived"
            );

            return $project->fresh();
        });
    }

    /**
     * Add an investigator to the project
     */
    public function addInvestigator(Project $project, int $userId, string $role, int $performedBy): ProjectInvestigator
    {
        return DB::transaction(function () use ($project, $userId, $role, $performedBy) {
            // Validation: user must be from same institution as PI
            $user = User::findOrFail($userId);
            if (!$user->sharesInstitutionWith($project->pi)) {
                throw new \InvalidArgumentException('Investigator must be from the same institution as the PI');
            }

            // Check if already an investigator
            if ($project->investigators()->where('user_id', $userId)->exists()) {
                throw new \InvalidArgumentException('User is already an investigator on this project');
            }

            $investigator = $project->investigators()->create([
                'user_id' => $userId,
                'role' => $role,
            ]);

            $this->logHistory(
                $project,
                'investigator_added',
                $performedBy,
                "Investigator '{$user->name}' added with role '{$role}'"
            );

            return $investigator;
        });
    }

    /**
     * Remove an investigator from the project
     */
public function removeInvestigator(Project $project, int $investigatorId, int $performedBy): void
{
    DB::transaction(function () use ($project, $investigatorId, $performedBy) {
        $investigator = $project->investigators()->findOrFail($investigatorId);

        // Store user name BEFORE deletion
        $userName = $investigator->user?->name ?? 'Unknown User';

        $investigator->delete();

        $this->logHistory(
            $project,
            'investigator_removed',
            $performedBy,
            "Investigator '{$userName}' removed from project"
        );
    });
}

    /**
     * Calculate project progress statistics
     */
    public function calculateProgress(Project $project): array
    {
        $milestones = $project->milestones()->with('status')->get();
        $totalMilestones = $milestones->count();

        if ($totalMilestones === 0) {
            return [
                'total_milestones' => 0,
                'completed_milestones' => 0,
                'pending_milestones' => 0,
                'overdue_milestones' => 0,
                'progress_percentage' => 0,
            ];
        }

        $completedMilestones = $milestones->filter(fn($m) => $m->status?->name === 'completed')->count();
        $overdueMilestones = $milestones->filter(function($m) {
            return $m->due_date < now() && $m->status?->name !== 'completed';
        })->count();

        return [
            'total_milestones' => $totalMilestones,
            'completed_milestones' => $completedMilestones,
            'pending_milestones' => $totalMilestones - $completedMilestones,
            'overdue_milestones' => $overdueMilestones,
            'progress_percentage' => $project->getProgressPercentage(),
        ];
    }

    /**
     * Get budget statistics
     */
    public function getBudgetStats(Project $project): array
    {
        $totalFunding = $project->getTotalFundingAmount();
        $totalExpenses = $project->getTotalExpenses();
        $remainingBudget = $project->getRemainingBudget();
        $budgetUtilization = $project->total_budget > 0
            ? round(($totalExpenses / $project->total_budget) * 100, 2)
            : 0;

        return [
            'total_budget' => $project->total_budget,
            'total_funding' => $totalFunding,
            'total_expenses' => $totalExpenses,
            'remaining_budget' => $remainingBudget,
            'budget_utilization_percentage' => $budgetUtilization,
            'is_over_budget' => $totalExpenses > $project->total_budget,
        ];
    }

    /**
     * Validate project timeline
     */
    public function validateTimeline(Project $project): array
    {
        $errors = [];

        // Check if end date is after start date
        if ($project->end_date <= $project->start_date) {
            $errors[] = 'End date must be after start date';
        }

        // Check if project has overdue milestones
        $overdueMilestones = $project->milestones()
            ->where('due_date', '<', now())
            ->whereHas('status', fn($q) => $q->where('name', '!=', 'completed'))
            ->count();

        if ($overdueMilestones > 0) {
            $errors[] = "{$overdueMilestones} milestone(s) are overdue";
        }

        // Check if project is overdue
        if ($project->isOverdue()) {
            $errors[] = 'Project end date has passed';
        }

        return [
            'is_valid' => count($errors) === 0,
            'errors' => $errors,
            'is_overdue' => $project->isOverdue(),
            'days_remaining' => $project->end_date ? now()->diffInDays($project->end_date, false) : null,
        ];
    }

    /**
     * Create project from approved proposal
     */
    public function createFromProposal(Proposal $proposal, User $creator): Project
    {
        if ($proposal->status?->name !== 'approved') {
            abort(422, 'Only approved proposals can be converted to projects.');
        }

        if ($proposal->project()->exists()) {
            abort(422, 'A project already exists for this proposal.');
        }

        return $this->create([
            'proposal_id' => $proposal->id,
            'title' => $proposal->title,
            'description' => $proposal->abstract,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'total_budget' => $proposal->budget,
            'budget_allocation' => $proposal->budget_allocation,
            'pi_id' => $proposal->submitted_by,
            'academic_year_id' => $proposal->academic_year_id,
            'research_center_id' => $proposal->research_center_id,
        ], $creator->id);
    }

    /**
     * Log history entry
     */
    private function logHistory(Project $project, string $action, int $userId, string $description, ?array $changes = null): void
    {
        ProjectHistory::create([
            'project_id' => $project->id,
            'action' => $action,
            'performed_by' => $userId,
            'description' => $description,
            'changes' => $changes,
        ]);
    }
}
