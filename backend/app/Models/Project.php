<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id', 'title', 'start_date', 'end_date', 'total_budget',
        'budget_allocation', 'cover_image_id', 'status_id', 'pi_id', 'academic_year_id'
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'total_budget'      => 'decimal:2',
        'budget_allocation' => 'array',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function pi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pi_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'cover_image_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function patents(): HasMany
    {
        return $this->hasMany(Patent::class);
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'project_files')
                    ->withTimestamps()
                    ->using(ProjectFile::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(Output::class);
    }

    public function linkedCommunityProblems(): HasMany
    {
        return $this->hasMany(CommunityProblem::class, 'linked_project_id');
    }
}
