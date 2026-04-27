<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalReviewScore extends Model
{
    use HasFactory;

    protected $fillable = ['proposal_reviewer_id', 'criterion_id', 'score', 'comments'];

    public function proposalReviewer()
    {
        return $this->belongsTo(ProposalReviewer::class);
    }

    public function criterion()
    {
        return $this->belongsTo(ReviewCriterion::class, 'criterion_id');
    }
}
