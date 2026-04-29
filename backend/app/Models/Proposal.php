<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id', 'type_id', 'title', 'abstract', 'objectives',
        'methodology', 'keywords', 'budget', 'budget_allocation',
        'status_change_comment', 'status_id', 'submitted_by',
        'submitted_at', 'approved_by', 'approved_at', 'academic_year_id',
        'file_id', 'ethics_file_id', 'ethics_approval_status_id'
    ];

    protected $casts = [
        'submitted_at'      => 'datetime',
        'approved_at'       => 'datetime',
        'budget'            => 'decimal:2',
        'budget_allocation' => 'array',
    ];

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProposalType::class, 'type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProposalStatus::class, 'status_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function ethicsFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'ethics_file_id');
    }

    public function ethicsApprovalStatus(): BelongsTo
    {
        return $this->belongsTo(EthicsApprovalStatus::class, 'ethics_approval_status_id');
    }

    public function investigators(): HasMany
    {
        return $this->hasMany(ProposalInvestigator::class);
    }

    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'proposal_reviewers')
                    ->withPivot(
                        'id', 'assigned_by', 'assigned_at', 'submitted_at',
                        'overall_score', 'overall_comments', 'decision_id'
                    )
                    ->withTimestamps()
                    ->using(ProposalReviewer::class)
                    ->as('reviewPivot');
    }

    public function financeChecks(): HasMany
    {
        return $this->hasMany(FinanceCheck::class);
    }

    public function ethicsRequests(): HasMany
    {
        return $this->hasMany(EthicsRequest::class);
    }

    public function detectionRequests(): MorphMany
    {
        return $this->morphMany(DetectionRequest::class, 'detectable');
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'proposal_files')
                    ->withTimestamps()
                    ->using(ProposalFile::class);
    }
}
