<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EthicsRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id', 'generated_pdf_path', 'submitted_to_irb',
        'approval_status_id', 'comments', 'version'
    ];

    protected function casts(): array
    {
        return [
            'submitted_to_irb' => 'boolean',
        ];
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function approvalStatus()
    {
        return $this->belongsTo(EthicsApprovalStatus::class, 'approval_status_id');
    }
}
