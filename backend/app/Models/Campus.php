<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'university_id'];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function faculties(): HasMany
    {
        return $this->hasMany(Faculty::class);
    }

    public function researchCenters(): HasMany
    {
        return $this->hasMany(ResearchCenter::class, 'parent_campus_id');
    }
}
