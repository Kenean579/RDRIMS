<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use HasFactory, \App\Traits\HierarchicalScope;

    protected $fillable = [
        'project_id', 'title', 'abstract', 'keywords', 'journal',
        'doi', 'scholar_url', 'publication_date', 'citation_count', 'file_id',
        'research_center_id'
    ];

    protected $casts = [
        'publication_date' => 'date',
        'citation_count'   => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

    public function getAuthorNamesAttribute(): string
    {
        return $this->authors->map(fn($a) => $a->user?->name ?? $a->external_author_name)->join(', ');
    }
}
