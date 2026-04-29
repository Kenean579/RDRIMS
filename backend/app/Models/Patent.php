<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'inventors', 'filing_date', 'patent_number', 'status_id'
    ];

    protected $casts = [
        'filing_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(PatentStatus::class, 'status_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'patent_files')
                    ->withTimestamps()
                    ->using(PatentFile::class);
    }
}
