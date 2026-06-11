<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'campus_id', 'logo_file_id'];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function researchCenters(): HasMany
    {
        return $this->hasMany(ResearchCenter::class, 'parent_faculty_id');
    }
}
