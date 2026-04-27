<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalFile extends Model
{
    use HasFactory;

    // No updated_at
    const UPDATED_AT = null;

    protected $fillable = ['proposal_id', 'file_id'];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
