<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoU extends Model
{
    use HasFactory;

    protected $table = 'mo_us';

    protected $fillable = [
        'partner_id', 'start_date', 'end_date', 'research_center_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class, 'research_center_id');
    }
}
