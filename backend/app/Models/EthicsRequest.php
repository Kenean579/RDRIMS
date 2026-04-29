<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EthicsRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id', 'generated_pdf_path', 'submitted_to_irb',
        'approval_status_id', 'comments', 'version'
    ];

    protected $casts = [
        'submitted_to_irb' => 'boolean',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function approvalStatus(): BelongsTo
    {
        return $this->belongsTo(EthicsApprovalStatus::class, 'approval_status_id');
    }
}
