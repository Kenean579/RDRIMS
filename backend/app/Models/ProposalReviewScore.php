<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalReviewScore extends Model
{
    use HasFactory;

    protected $fillable = ['proposal_reviewer_id', 'criterion_id', 'score', 'comments'];

    public function proposalReviewer(): BelongsTo
    {
        return $this->belongsTo(ProposalReviewer::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(ReviewCriterion::class);
    }
}
