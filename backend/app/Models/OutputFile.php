<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutputFile extends Pivot
{
    use HasFactory;

    protected $fillable = ['output_id', 'file_id'];

    public function output(): BelongsTo
    {
        return $this->belongsTo(Output::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}