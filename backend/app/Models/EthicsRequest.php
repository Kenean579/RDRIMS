<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EthicsRequest extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HasDynamicStatus, \App\Traits\HierarchicalScope;

    public $statusModelMapping = \App\Models\EthicsApprovalStatus::class;

    protected $fillable = [
        'proposal_id', 'generated_pdf_path', 'submitted_to_irb',
        'approval_status_id', 'comments', 'version',
        'reviewer_id', 'reviewed_at', 'created_by', 'updated_by'
    ];

    protected $guarded = [
        'approval_status_id', 'reviewer_id', 'reviewed_at'
    ];

    protected $casts = [
        'submitted_to_irb' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function approvalStatus(): BelongsTo
    {
        return $this->belongsTo(EthicsApprovalStatus::class, 'approval_status_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if ethics request is pending
     */
    public function isPending(): bool
    {
        return $this->approvalStatus?->name === 'pending';
    }

    /**
     * Check if ethics request is approved
     */
    public function isApproved(): bool
    {
        return $this->approvalStatus?->name === 'approved';
    }

    /**
     * Check if ethics request is rejected
     */
    public function isRejected(): bool
    {
        return $this->approvalStatus?->name === 'rejected';
    }

    /**
     * Check if ethics request needs revision
     */
    public function needsRevision(): bool
    {
        return $this->approvalStatus?->name === 'needs_revision';
    }

    /**
     * Check if ethics request is reviewed
     */
    public function isReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    /**
     * Check if ethics request can be edited
     */
    public function canEdit(): bool
    {
        return $this->isPending() || $this->needsRevision();
    }

    /**
     * Check if decision can be made
     */
    public function canDecide(): bool
    {
        return $this->submitted_to_irb && !$this->isReviewed();
    }

    /**
     * Scope: For university (tenant isolation)
     */
    public function scopeForUniversity($query, int $universityId)
    {
        return $query->whereHas('proposal.submittedBy', fn($q) => $q->where('university_id', $universityId));
    }

    /**
     * Scope: By status
     */
    public function scopeByStatus($query, string $statusName)
    {
        return $query->whereHas('approvalStatus', fn($q) => $q->where('name', $statusName));
    }

    /**
     * Scope: Reviewed
     */
    public function scopeReviewed($query)
    {
        return $query->whereNotNull('reviewed_at');
    }

    /**
     * Scope: Pending
     */
    public function scopePending($query)
    {
        return $query->whereNull('reviewed_at');
    }
}
