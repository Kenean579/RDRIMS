<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use HasFactory, \App\Traits\HasDynamicStatus;

    protected $fillable = [
        'project_id', 'title', 'abstract', 'keywords', 'journal_name',
        'doi', 'url', 'publication_date', 'volume', 'issue', 'pages',
        'access_type_id', 'status_id', 'cover_image_id'
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(PublicationStatus::class, 'status_id');
    }

    public function accessType(): BelongsTo
    {
        return $this->belongsTo(PublicationAccessType::class, 'access_type_id');
    }

    protected $casts = [
        'publication_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'cover_image_id');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(PublicationAuthor::class);
    }
}
