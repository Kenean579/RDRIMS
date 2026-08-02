<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
/**
     * @property int $id
     * @property string $file_path
     * @property int $version
     * @property int $uploaded_by
     * @property bool $is_public
     * @property string|null $mime_type
     * @property string|null $file_hash
     * @property array|null $metadata
     * @property string|null $original_filename
     * @property \Illuminate\Support\Carbon|null $created_at
     */
    use HasFactory;

    public $timestamps = false; // only created_at

    protected $fillable = [
        'file_path', 'version', 'uploaded_by', 'is_public', 'mime_type',
        'file_hash', 'metadata', 'original_filename', 'created_at'
    ];

    protected $casts = [
        'is_public'  => 'boolean',
        'created_at' => 'datetime',
        'metadata'   => 'array',
    ];

    protected $appends = ['url'];

    /**
     * Canonical browser URL for files explicitly marked public.
     */
    public function getUrlAttribute(): ?string
    {
        if (! $this->is_public || ! $this->file_path) {
            return null;
        }

        return url(Storage::disk('public')->url($this->file_path));
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function proposals(): BelongsToMany
    {
        return $this->belongsToMany(Proposal::class, 'proposal_files')
                    ->withTimestamps()
                    ->using(ProposalFile::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_files')
                    ->withTimestamps()
                    ->using(ProjectFile::class);
    }

    public function outputs(): BelongsToMany
    {
        return $this->belongsToMany(Output::class, 'output_files')
                    ->withTimestamps()
                    ->using(OutputFile::class);
    }

    public function patents(): BelongsToMany
    {
        return $this->belongsToMany(Patent::class, 'patent_files')
                    ->withTimestamps()
                    ->using(PatentFile::class);
    }

    public function versions()
    {
        // For file versioning, we need to find all files with the same original filename
        // This is a simplified version - in production you might want a separate file_versions table
        return self::where('original_filename', $this->original_filename)
                   ->orderBy('version', 'desc');
    }
}
