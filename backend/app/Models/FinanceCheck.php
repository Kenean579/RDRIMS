<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceCheck extends Model
{
    use HasFactory;

    protected $fillable = ['proposal_id', 'checker_id', 'status_id', 'comments', 'checked_at'];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(FinanceCheckStatus::class, 'status_id');
    }
}
