<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class File extends Model
{
    use HasFactory;

    public $timestamps = false; // only created_at

    protected $fillable = ['file_path', 'version', 'uploaded_by', 'is_public', 'created_at'];

    protected $casts = [
        'is_public'  => 'boolean',
        'created_at' => 'datetime',
    ];

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
}
