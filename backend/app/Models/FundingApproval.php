<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingApproval extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $updateable = false;

    protected $fillable = [
        'funding_id',
        'action',
        'approved_by',
        'approved_at',
        'comments',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function funding(): BelongsTo
    {
        return $this->belongsTo(Funding::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
