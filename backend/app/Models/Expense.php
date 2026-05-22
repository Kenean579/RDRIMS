<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory, \App\Traits\HasDynamicStatus;

    protected $fillable = [
        'project_id', 'title', 'amount', 'category_id', 'expense_date', 'status_id',
        'description', 'approved_by', 'approved_at', 'evidence_file_id'
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(ExpenseStatus::class, 'status_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(File::class, 'evidence_file_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
