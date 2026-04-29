<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'abstract', 'keywords', 'journal',
        'doi', 'scholar_url', 'publication_date', 'citation_count', 'file_id'
    ];

    protected $casts = [
        'publication_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function authors(): HasMany
    {
        return $this->hasMany(PublicationAuthor::class);
    }
}
