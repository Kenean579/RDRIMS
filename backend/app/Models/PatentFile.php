<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatentFile extends Pivot
{
    use HasFactory;

    protected $fillable = ['patent_id', 'file_id'];

    public function patent(): BelongsTo
    {
        return $this->belongsTo(Patent::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}