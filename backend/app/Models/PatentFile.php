<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatentFile extends Pivot
{
    use HasFactory;

    protected $fillable = ['patent_id', 'file_id'];
}
