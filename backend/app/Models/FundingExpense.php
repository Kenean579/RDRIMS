<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundingExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'funding_id',
        'budget_category_id',
        'expense_category_id',
        'reference_number',
        'description',
        'amount',
        'currency',
        'expense_date',
        'submitted_by',
        'approved_by',
        'approved_at',
        'status',
        'approval_notes',
    ];

    protected $guarded = [
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function funding(): BelongsTo
    {
        return $this->belongsTo(Funding::class);
    }

    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Get pending expenses
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get approved expenses
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Get rejected expenses
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
