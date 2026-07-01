<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EthicsRequest extends Model
{
    use HasFactory, \App\Traits\HasDynamicStatus;

    public $statusModelMapping = \App\Models\EthicsApprovalStatus::class;

    protected $fillable = [
        'proposal_id', 'generated_pdf_path', 'submitted_to_irb',
        'approval_status_id', 'comments', 'version',
        'reviewer_id', 'reviewed_at'
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
}
