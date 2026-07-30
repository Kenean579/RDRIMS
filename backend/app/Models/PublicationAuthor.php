<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationAuthor extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'user_id',
        'external_author_name',
        'external_institution',
        'author_order',
        'contribution_role',
        'is_corresponding',
    ];

    protected $casts = [
        'is_corresponding' => 'boolean',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the author's display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? $this->external_author_name ?? 'Unknown Author';
    }

    /**
     * Check if this is an internal author
     */
    public function isInternal(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Check if this is the first author
     */
    public function isFirstAuthor(): bool
    {
        return $this->author_order === 1 || $this->contribution_role === 'first_author';
    }
}
