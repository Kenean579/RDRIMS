<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewCriterion extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'max_score', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
