<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HierarchicalScope;

class ResearchCenter extends Model
{
    use HasFactory, HierarchicalScope;

    protected $fillable = [
        'name', 'code', 'director_id', 'logo_file_id',
        'parent_university_id', 'parent_campus_id', 'parent_faculty_id', 'parent_department_id',
        'description'
    ];

    protected $with = ['university'];

    /**
     * Get the university this research center belongs to.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'parent_university_id');
    }

    /**
     * Get the campus this research center belongs to (if campus-level).
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'parent_campus_id');
    }

    /**
     * Get the faculty this research center belongs to (if department-level).
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'parent_faculty_id');
    }

    /**
     * Get the department this research center belongs to (if department-level).
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    /**
     * Get the director of this research center.
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Get the logo file for this research center.
     */
    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    /**
     * Get all users affiliated with this research center.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_research_centers')
                    ->withPivot('center_role_id')
                    ->withTimestamps()
                    ->using(UserResearchCenter::class);
    }

    /**
     * Get the university ID this research center belongs to (through hierarchy).
     */
    public function getUniversityIdAttribute(): ?int
    {
        return $this->parent_university_id;
    }

    /**
     * Determine if this research center belongs to a specific university.
     */
    public function belongsToUniversity(int $universityId): bool
    {
        return $this->parent_university_id === $universityId;
    }

    /**
     * Determine if this is a university-level research center (no campus/faculty/dept).
     */
    public function isUniversityLevel(): bool
    {
        return $this->parent_university_id !== null
            && $this->parent_campus_id === null
            && $this->parent_faculty_id === null
            && $this->parent_department_id === null;
    }

    /**
     * Determine if this is a campus-level research center.
     */
    public function isCampusLevel(): bool
    {
        return $this->parent_university_id !== null
            && $this->parent_campus_id !== null
            && $this->parent_faculty_id === null
            && $this->parent_department_id === null;
    }

    /**
     * Determine if this is a department-level research center (deepest hierarchy).
     */
    public function isDepartmentLevel(): bool
    {
        return $this->parent_university_id !== null
            && $this->parent_campus_id !== null
            && $this->parent_faculty_id !== null
            && $this->parent_department_id !== null;
    }
}
