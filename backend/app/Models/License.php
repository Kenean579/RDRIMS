<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'patent_id', 'company_name', 'start_date', 'end_date', 'royalty_rate'
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'royalty_rate' => 'decimal:2',
    ];

    public function patent(): BelongsTo
    {
        return $this->belongsTo(Patent::class);
    }
}
