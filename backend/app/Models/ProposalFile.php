<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProposalFile extends Pivot
{
    use HasFactory;

    protected $fillable = ['proposal_id', 'file_id'];
}
