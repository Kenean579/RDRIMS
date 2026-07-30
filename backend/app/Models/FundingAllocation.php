<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'funding_id',
        'budget_category_id',
        'allocated_amount',
        'used_amount',
        'revised_amount',
        'revision_approved_by',
        'revision_approved_at',
        'revision_notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'revised_amount' => 'decimal:2',
        'revision_approved_at' => 'datetime',
    ];

    public function funding(): BelongsTo
    {
        return $this->belongsTo(Funding::class);
    }

    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function revisionApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revision_approved_by');
    }

    /**
     * Get the current budget amount (revised or original)
     */
    public function getCurrentBudget(): float
    {
        return (float) ($this->revised_amount ?? $this->allocated_amount);
    }

    /**
     * Get remaining budget for this allocation
     */
    public function getRemainingBudget(): float
    {
        return (float) ($this->getCurrentBudget() - $this->used_amount);
    }

    /**
     * Get budget utilization percentage
     */
    public function getUtilizationPercentage(): float
    {
        $current = $this->getCurrentBudget();
        if ($current == 0) return 0;
        return round(($this->used_amount / $current) * 100, 2);
    }
}
