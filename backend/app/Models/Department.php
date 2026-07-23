<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'faculty_id', 'logo_file_id'];

    protected $with = ['faculty'];

    /**
     * Get the faculty this department belongs to.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the logo file for this department.
     */
    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    /**
     * Get all users in this department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all research centers under this department.
     */
    public function researchCenters(): HasMany
    {
        return $this->hasMany(ResearchCenter::class, 'parent_department_id');
    }

    /**
     * Get the university this department belongs to (through faculty → campus → university).
     */
    public function getUniversityIdAttribute(): ?int
    {
        return $this->faculty?->campus?->university_id;
    }

    /**
     * Determine if this department belongs to a specific university.
     */
    public function belongsToUniversity(int $universityId): bool
    {
        return $this->faculty?->campus?->university_id === $universityId;
    }

    /**
     * Determine if this department belongs to a specific faculty.
     */
    public function belongsToFaculty(int $facultyId): bool
    {
        return $this->faculty_id === $facultyId;
    }
}
