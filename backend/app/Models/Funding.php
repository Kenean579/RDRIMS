<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Funding extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HasDynamicStatus;

    /**
     * @property int $id
     * @property int $university_id
     * @property int $funding_source_id
     * @property int|null $project_id
     * @property int|null $proposal_id
     * @property int $status_id
     * @property string $reference_number
     * @property string $title
     * @property string|null $description
     * @property float $total_amount
     * @property string $currency
     * @property \Illuminate\Support\Carbon $start_date
     * @property \Illuminate\Support\Carbon $end_date
     * @property bool $is_internal
     * @property int $created_by
     * @property int|null $approved_by
     * @property \Illuminate\Support\Carbon|null $approved_at
     */
    protected $fillable = [
        'university_id',
        'funding_source_id',
        'project_id',
        'proposal_id',
        'reference_number',
        'title',
        'description',
        'total_amount',
        'currency',
        'start_date',
        'end_date',
        'is_internal',
        'created_by',
    ];

    protected $guarded = [
        'status_id',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'approved_at' => 'datetime',
        'is_internal' => 'boolean',
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(FundingStatus::class, 'status_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(FundingAllocation::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(FundingExpense::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(FundingApproval::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(FundingHistory::class);
    }

    /**
     * Calculate total used amount across all allocations
     */
    public function getTotalUsedAmount(): float
    {
        return (float) $this->allocations()->sum('used_amount');
    }

    /**
     * Calculate remaining balance
     */
    public function getRemainingBalance(): float
    {
        return (float) $this->total_amount - $this->getTotalUsedAmount();
    }

    /**
     * Calculate allocation budget for a category
     */
    public function getAllocationForCategory($categoryId): ?FundingAllocation
    {
        return $this->allocations()->where('budget_category_id', $categoryId)->first();
    }

    /**
     * Scope: Filter by university
     */
    public function scopeForUniversity($query, $universityId)
    {
        return $query->where('university_id', $universityId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $statusName)
    {
        return $query->whereHas('status', fn($q) => $q->where('name', $statusName));
    }

    /**
     * Scope: Active fundings
     */
    public function scopeActive($query)
    {
        return $query->byStatus('approved');
    }

    /**
     * Scope: Pending fundings
     */
    public function scopePending($query)
    {
        return $query->whereIn('status_id', function($q) {
            $q->select('id')->from('funding_statuses')
                ->whereIn('name', ['draft', 'submitted', 'under_review']);
        });
    }
}
