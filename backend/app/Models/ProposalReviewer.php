<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalReviewer extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id', 'reviewer_id', 'assigned_by', 'assigned_at',
        'submitted_at', 'overall_score', 'overall_comments', 'decision_id'
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'submitted_at' => 'datetime',
            'overall_score' => 'decimal:2',
        ];
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function decision()
    {
        return $this->belongsTo(ReviewDecision::class, 'decision_id');
    }

    public function scores()
    {
        return $this->hasMany(ProposalReviewScore::class);
    }
}
