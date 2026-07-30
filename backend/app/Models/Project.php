<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HasDynamicStatus, \App\Traits\HierarchicalScope;

    protected $fillable = [
        'proposal_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'total_budget',
        'budget_allocation',
        'cover_image_id',
        'pi_id',
        'academic_year_id',
        'research_center_id',
        'created_by',
        'updated_by',
    ];

    protected $guarded = [
        'status_id',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'total_budget'      => 'decimal:2',
        'budget_allocation' => 'array',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function pi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pi_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class, 'research_center_id');
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'cover_image_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function patents(): HasMany
    {
        return $this->hasMany(Patent::class);
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'project_files')
                    ->withTimestamps()
                    ->using(ProjectFile::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(Output::class);
    }

    public function linkedCommunityProblems(): HasMany
    {
        return $this->hasMany(CommunityProblem::class, 'linked_project_id');
    }

    public function investigators(): HasMany
    {
        return $this->hasMany(ProjectInvestigator::class);
    }

    public function fundings(): HasMany
    {
        return $this->hasMany(Funding::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProjectHistory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calculate project progress percentage based on completed milestones
     */
    public function getProgressPercentage(): float
    {
        $totalMilestones = $this->milestones()->count();
        
        if ($totalMilestones === 0) {
            return 0.0;
        }

        $completedMilestones = $this->milestones()
            ->whereHas('status', fn($q) => $q->where('name', 'completed'))
            ->count();

        return round(($completedMilestones / $totalMilestones) * 100, 2);
    }

    /**
     * Calculate total budget from all fundings
     */
    public function getTotalFundingAmount(): float
    {
        return (float) $this->fundings()
            ->whereHas('status', fn($q) => $q->where('name', 'approved'))
            ->sum('total_amount');
    }

    /**
     * Calculate total expenses
     */
    public function getTotalExpenses(): float
    {
        return (float) $this->expenses()
            ->whereNotNull('approved_by')
            ->sum('amount');
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudget(): float
    {
        return $this->total_budget - $this->getTotalExpenses();
    }

    /**
     * Check if project is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->isAfter($this->end_date) && 
               !in_array($this->status?->name, ['completed', 'closed']);
    }

    /**
     * Check if project can be completed
     */
    public function canComplete(): bool
    {
        // Must be in active status
        if ($this->status?->name !== 'active') {
            return false;
        }

        // All milestones must be completed
        $pendingMilestones = $this->milestones()
            ->whereHas('status', fn($q) => $q->whereNotIn('name', ['completed', 'cancelled']))
            ->count();

        return $pendingMilestones === 0;
    }

    /**
     * Check if user is project member (PI or investigator)
     */
    public function isMember(int $userId): bool
    {
        if ($this->pi_id === $userId) {
            return true;
        }

        return $this->investigators()->where('user_id', $userId)->exists();
    }

    /**
     * Scope: Active projects
     */
    public function scopeActive($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('name', 'active'));
    }

    /**
     * Scope: Completed projects
     */
    public function scopeCompleted($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('name', 'completed'));
    }

    /**
     * Scope: Overdue projects
     */
    public function scopeOverdue($query)
    {
        return $query->where('end_date', '<', now())
            ->whereHas('status', fn($q) => $q->whereNotIn('name', ['completed', 'closed']));
    }

    /**
     * Scope: By status name
     */
    public function scopeByStatus($query, string $statusName)
    {
        return $query->whereHas('status', fn($q) => $q->where('name', $statusName));
    }

    /**
     * Scope: By PI
     */
    public function scopeByPI($query, int $piId)
    {
        return $query->where('pi_id', $piId);
    }

    /**
     * Scope: For university (tenant isolation)
     */
    public function scopeForUniversity($query, int $universityId)
    {
        return $query->whereHas('pi', fn($q) => $q->where('university_id', $universityId));
    }
}
