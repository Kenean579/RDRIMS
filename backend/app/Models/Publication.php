<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\HierarchicalScope;

    protected $fillable = [
        'project_id',
        'type_id',
        'title',
        'abstract',
        'keywords',
        'journal',
        'volume',
        'issue',
        'pages',
        'publisher',
        'conference_name',
        'doi',
        'isbn',
        'issn',
        'scholar_url',
        'publication_date',
        'citation_count',
        'file_id',
        'research_center_id',
        'created_by',
        'updated_by',
    ];

    protected $guarded = [
        'status_id',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'verified_at' => 'datetime',
        'citation_count' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(PublicationStatus::class, 'status_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PublicationType::class, 'type_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class, 'research_center_id');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(PublicationAuthor::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PublicationHistory::class);
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
     * Get author names as comma-separated string
     */
    public function getAuthorNamesAttribute(): string
    {
        return $this->authors->map(fn($a) => $a->user?->name ?? $a->external_author_name)->join(', ');
    }

    /**
     * Check if publication has at least one internal author
     */
    public function hasInternalAuthor(): bool
    {
        return $this->authors()->whereNotNull('user_id')->exists();
    }

    /**
     * Check if publication is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status?->name === 'draft';
    }

    /**
     * Check if publication is published
     */
    public function isPublished(): bool
    {
        return $this->status?->name === 'published';
    }

    /**
     * Check if publication is verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Check if publication can be submitted
     */
    public function canSubmit(): bool
    {
        return $this->isDraft() && $this->hasInternalAuthor();
    }

    /**
     * Check if publication can be published
     */
    public function canPublish(): bool
    {
        return $this->status?->name === 'accepted' && $this->isVerified();
    }

    /**
     * Scope: Published publications
     */
    public function scopePublished($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('name', 'published'));
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, string $typeName)
    {
        return $query->whereHas('type', fn($q) => $q->where('name', $typeName));
    }

    /**
     * Scope: By year
     */
    public function scopeByYear($query, int $year)
    {
        return $query->whereYear('publication_date', $year);
    }

    /**
     * Scope: By author (user)
     */
    public function scopeByAuthor($query, int $userId)
    {
        return $query->whereHas('authors', fn($q) => $q->where('user_id', $userId));
    }

    /**
     * Scope: For university (tenant isolation)
     */
    public function scopeForUniversity($query, int $universityId)
    {
        return $query->whereHas('project.pi', fn($q) => $q->where('university_id', $universityId));
    }
}
