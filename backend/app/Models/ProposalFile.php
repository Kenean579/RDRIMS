<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalFile extends Pivot
{
    use HasFactory;

    protected $fillable = ['proposal_id', 'file_id'];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}