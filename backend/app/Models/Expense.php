<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'amount', 'budget_category',
        'description', 'approved_by', 'approved_at', 'research_center_id'
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'approved_at'  => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class, 'research_center_id');
    }
}
