<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProposalReviewer extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'proposal_id', 'reviewer_id', 'assigned_by', 'assigned_at',
        'submitted_at', 'overall_score', 'overall_comments', 'decision_id'
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(ReviewDecision::class, 'decision_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ProposalReviewScore::class, 'proposal_reviewer_id');
    }
}
