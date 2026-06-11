<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'location', 'logo_file_id'];

    protected $casts = [
        'location' => 'string',
    ];

    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function campuses(): HasMany
    {
        return $this->hasMany(Campus::class);
    }

    public function researchCenters(): HasMany
    {
        return $this->hasMany(ResearchCenter::class, 'parent_university_id');
    }
}
