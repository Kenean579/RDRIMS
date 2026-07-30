<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingHistory extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $updateable = false;

    protected $fillable = [
        'funding_id',
        'action',
        'performed_by',
        'changes',
        'description',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function funding(): BelongsTo
    {
        return $this->belongsTo(Funding::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
