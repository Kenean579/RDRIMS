<?php

namespace App\Models;

use App\Models\User;
use App\Traits\HierarchicalScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    use HasFactory, HierarchicalScope;

    protected $fillable = [
        'title',
        'description',
        'deadline',
        'thematic_areas',
        'created_by',
        'status_id',
        'academic_year_id',
        'guideline_file_id',
        'community_problem_id',
        'university_id',
        'research_center_id',
        'campus_id',
        'faculty_id',
        'department_id'
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CallStatus::class, 'status_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function communityProblem(): BelongsTo
    {
        return $this->belongsTo(CommunityProblem::class, 'community_problem_id');
    }

    public function guidelineFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'guideline_file_id');
    }

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class, 'research_center_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * Scope a query to only include calls visible to the given user.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Include calls targeted globally
            $q->whereNull('university_id');

            if ($user->hasRole('research_admin')) {
                $q->orWhere('university_id', $user->university_id)
                  ->orWhereHas('createdBy', fn($u) => $u->where('university_id', $user->university_id));
            }
            if ($user->hasRole('campus_admin')) {
                $q->orWhere('campus_id', $user->campus_id);
            }
            if ($user->hasRole('faculty_admin')) {
                $q->orWhere('faculty_id', $user->faculty_id);
            }
            if ($user->hasRole('department_head')) {
                $q->orWhere('department_id', $user->department_id);
            }
            if ($user->hasRole('director')) {
                $centerIds = \Illuminate\Support\Facades\DB::table('user_research_centers')
                    ->where('user_id', $user->id)->pluck('research_center_id');
                if ($centerIds->isNotEmpty()) {
                    $q->orWhereIn('research_center_id', $centerIds);
                }
            }

            // Default scoping for researchers, reviewers, students, etc.
            if ($user->hasRole('researcher', 'reviewer', 'student', 'guest')) {
                $q->orWhere('university_id', $user->university_id);
                
                if ($user->department?->faculty?->campus_id) {
                    $q->orWhere('campus_id', $user->department->faculty->campus_id);
                }
                if ($user->department?->faculty_id) {
                    $q->orWhere('faculty_id', $user->department->faculty_id);
                }
                if ($user->department_id) {
                    $q->orWhere('department_id', $user->department_id);
                }
            }
        });
    }
}
