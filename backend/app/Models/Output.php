<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Output extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HasDynamicStatus, \App\Traits\HierarchicalScope;

    protected $fillable = [
        'category_id', 'student_level_id', 'subtype_id', 'proposal_id',
        'title', 'abstract', 'partner_id', 'project_id', 'status_id',
        'start_date', 'end_date', 'feedback', 'academic_year_id', 'budget',
        'research_center_id', 'created_by', 'updated_by', 'verified_by', 'verified_at'
    ];

    protected $guarded = [
        'status_id', 'verified_by', 'verified_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'budget'     => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(OutputCategory::class, 'category_id');
    }

    public function studentLevel(): BelongsTo
    {
        return $this->belongsTo(StudentLevel::class, 'student_level_id');
    }

    public function subtype(): BelongsTo
    {
        return $this->belongsTo(OutputSubtype::class, 'subtype_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OutputStatus::class, 'status_id');
    }

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class, 'research_center_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'output_participants', 'output_id', 'user_id')
                    ->withPivot('participant_type_id')
                    ->withTimestamps()
                    ->using(OutputParticipant::class);
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'output_files')
                    ->withPivot('created_at')
                    ->using(OutputFile::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'output_participants', 'output_id', 'user_id')
                    ->withPivot('participant_type_id')
                    ->withTimestamps()
                    ->using(OutputParticipant::class);
    }

    public function participantsWithType(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'output_participants', 'output_id', 'user_id')
                    ->withPivot('participant_type_id')
                    ->withTimestamps()
                    ->using(OutputParticipant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if output is verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Check if output is draft
     */
    public function isDraft(): bool
    {
        return $this->status?->name === 'draft';
    }

    /**
     * Check if output is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status?->name === 'submitted';
    }

    /**
     * Check if output is published
     */
    public function isPublished(): bool
    {
        return $this->status?->name === 'published';
    }

    /**
     * Check if output can be submitted
     */
    public function canSubmit(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if output can be published
     */
    public function canPublish(): bool
    {
        return $this->isVerified() && ($this->status?->name === 'approved');
    }

    /**
     * Scope: For university (tenant isolation)
     */
    public function scopeForUniversity($query, int $universityId)
    {
        return $query->whereHas('participants.user', fn($q) => $q->where('university_id', $universityId))
            ->orWhereHas('project.pi', fn($q) => $q->where('university_id', $universityId));
    }

    /**
     * Scope: By status
     */
    public function scopeByStatus($query, string $statusName)
    {
        return $query->whereHas('status', fn($q) => $q->where('name', $statusName));
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, string $categoryName)
    {
        return $query->whereHas('category', fn($q) => $q->where('name', $categoryName));
    }

    /**
     * Scope: Verified outputs
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }
}
