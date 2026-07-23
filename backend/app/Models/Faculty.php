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

    protected $with = ['campus'];

    /**
     * Get the campus this faculty belongs to.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the logo file for this faculty.
     */
    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    /**
     * Get all departments under this faculty.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get all research centers under this faculty.
     */
    public function researchCenters(): HasMany
    {
        return $this->hasMany(ResearchCenter::class, 'parent_faculty_id');
    }

    /**
     * Get the university this faculty belongs to (through campus).
     */
    public function getUniversityIdAttribute(): ?int
    {
        return $this->campus?->university_id;
    }

    /**
     * Determine if this faculty belongs to a specific university.
     */
    public function belongsToUniversity(int $universityId): bool
    {
        return $this->campus?->university_id === $universityId;
    }

    /**
     * Determine if this faculty belongs to a specific campus.
     */
    public function belongsToCampus(int $campusId): bool
    {
        return $this->campus_id === $campusId;
    }
}
