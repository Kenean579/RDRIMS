<?php

namespace App\Models;

use App\Models\User;
use App\Traits\BelongsToUniversity;
use App\Traits\HierarchicalScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    use HasFactory, SoftDeletes, HierarchicalScope, BelongsToUniversity;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'title',
        'description',
        'deadline',
        'thematic_areas',
        'created_by',
        'status_id',
        'academic_year_id',
        'guideline_file_id',
        'university_id',
        'research_center_id',
        'campus_id',
        'faculty_id',
        'department_id',
        'published_at',
        'opens_at',
        'closes_at',
        'is_public',
        'is_featured',
        'metadata',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'deadline'      => 'date',
        'published_at'  => 'datetime',
        'opens_at'      => 'datetime',
        'closes_at'     => 'datetime',
        'is_public'     => 'boolean',
        'is_featured'   => 'boolean',
        'metadata'      => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User who created the call.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Call status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(CallStatus::class, 'status_id');
    }

    /**
     * Academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Guideline document.
     */
    public function guidelineFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'guideline_file_id');
    }

    /**
     * University.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    /**
     * Research center.
     */
    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(
            ResearchCenter::class,
            'research_center_id'
        );
    }

    /**
     * Campus.
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Faculty.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Submitted proposals.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Only published calls.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Only open calls.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->whereNotNull('opens_at')
            ->whereNotNull('closes_at')
            ->where('opens_at', '<=', now())
            ->where('closes_at', '>=', now());
    }

    /**
     * Only public calls.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope calls visible to the authenticated user.
     * 
     * IMPORTANT: This scope is PRESERVED for backward compatibility.
     * Dashboard and other modules depend on this method signature.
     * 
     * The implementation uses complex role-based filtering logic.
     * For new features, prefer using CallService->getVisibleCalls() or
     * policy-based authorization instead.
     * 
     * Business Logic:
     * - Super Admin: sees all calls (though policy now denies access)
     * - Research Admin: university-level calls
     * - Campus Admin: campus-level calls
     * - Faculty Admin: faculty-level calls
     * - Department Head: department-level calls
     * - Director: research center calls
     * - Researcher/Reviewer/Student/Guest: calls matching their hierarchy
     * 
     * @param Builder $query
     * @param User $user
     * @return Builder
     * 
     * @see CallService::getVisibleCalls() For cleaner implementation
     * @deprecated Consider refactoring to CallService in future major version
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        // Super Admin can view all calls.
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {

            // Global calls.
            $q->whereNull('university_id');

            /*
            |--------------------------------------------------------------------------
            | Research Administration
            |--------------------------------------------------------------------------
            */

            if ($user->hasRole('research_admin')) {

                $q->orWhere(
                    'university_id',
                    $user->resolvedUniversityId()
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Campus Administration
            |--------------------------------------------------------------------------
            */

            if (
                $user->hasRole('campus_admin') &&
                $user->campus_id
            ) {
                $q->orWhere('campus_id', $user->campus_id);
            }

            /*
            |--------------------------------------------------------------------------
            | Faculty Administration
            |--------------------------------------------------------------------------
            */

            if (
                $user->hasRole('faculty_admin') &&
                $user->faculty_id
            ) {
                $q->orWhere('faculty_id', $user->faculty_id);
            }

            /*
            |--------------------------------------------------------------------------
            | Department Administration
            |--------------------------------------------------------------------------
            */

            if (
                $user->hasRole('department_head') &&
                $user->department_id
            ) {
                $q->orWhere(
                    'department_id',
                    $user->department_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Research Center Director
            |--------------------------------------------------------------------------
            */

            if ($user->hasRole('director')) {

                $centerIds = $user->researchCenters()
                    ->pluck('research_centers.id');

                if ($centerIds->isNotEmpty()) {
                    $q->orWhereIn(
                        'research_center_id',
                        $centerIds
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Researchers / Reviewers / Students / Guests
            |--------------------------------------------------------------------------
            */

            if (
                $user->hasAnyRole([
                    'researcher',
                    'reviewer',
                    'student',
                    'guest'
                ])
            ) {

                if ($user->resolvedUniversityId()) {
                    $q->orWhere(
                        'university_id',
                        $user->resolvedUniversityId()
                    );
                }

                if ($user->campus_id) {
                    $q->orWhere(
                        'campus_id',
                        $user->campus_id
                    );
                }

                if ($user->faculty_id) {
                    $q->orWhere(
                        'faculty_id',
                        $user->faculty_id
                    );
                }

                if ($user->department_id) {
                    $q->orWhere(
                        'department_id',
                        $user->department_id
                    );
                }
            }
        });
    }
}
