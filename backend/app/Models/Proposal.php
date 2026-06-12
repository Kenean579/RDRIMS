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
    use HasFactory, \App\Traits\HasDynamicStatus, \App\Traits\HierarchicalScope;

    /**
     * @property int $id
     * @property int $call_id
     * @property int|null $type_id
     * @property string $title
     * @property string|null $abstract
     * @property string|null $objectives
     * @property string|null $methodology
     * @property string|null $keywords
     * @property string|null $budget
     * @property array|null $budget_allocation
     * @property string|null $status_change_comment
     * @property int $status_id
     * @property int|null $submitted_by
     * @property \Illuminate\Support\Carbon|null $submitted_at
     * @property int|null $approved_by
     * @property \Illuminate\Support\Carbon|null $approved_at
     * @property int|null $academic_year_id
     * @property int|null $file_id
     * @property int|null $ethics_file_id
     * @property int|null $ethics_approval_status_id
     * @property int|null $research_center_id
     * @property float|null $originality_score
     * @property string|null $plagiarism_report_url
     */
    protected $fillable = [
        'call_id',
         'type_id',
         'title',
         'abstract',
         'objectives',
         'methodology',
         'keywords',
         'budget',
          'budget_allocation',
         'status_change_comment',
         'status_id',
          'submitted_by',
         'submitted_at',
          'approved_by',
          'approved_at',
          'academic_year_id',
         'file_id',
          'ethics_file_id',
          'ethics_approval_status_id',
          'research_center_id',
          'originality_score',
          'plagiarism_report_url'
    ];

    protected $casts = [
        'submitted_at'      => 'datetime',
        'approved_at'       => 'datetime',
        'budget'            => 'decimal:2',
        'budget_allocation' => 'array',
        'originality_score' => 'decimal:2',
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

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class, 'research_center_id');
    }

    public function investigators(): HasMany
    {
        return $this->hasMany(ProposalInvestigator::class);
    }

    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'proposal_reviewers', 'proposal_id', 'reviewer_id')
            ->withPivot(
                'id', 'assigned_by', 'assigned_at', 'submitted_at',
                'overall_score', 'overall_comments', 'decision_id'
            )
            ->withTimestamps()
            ->using(ProposalReviewer::class);
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
