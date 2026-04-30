<?php
// app/Models/Call.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Call for Proposals
 *
 * Represents a funding/research call issued by the university.
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $deadline
 * @property string $thematic_areas
 * @property int $created_by
 * @property int $status_id
 * @property int|null $academic_year_id
 * @property int|null $guideline_file_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\CallStatus $status
 * @property-read \App\Models\AcademicYear|null $academicYear
 * @property-read \App\Models\File|null $guidelineFile
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Proposal[] $proposals
 */
class Call extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title', 'description', 'deadline', 'thematic_areas', 'created_by',
        'status_id', 'academic_year_id', 'guideline_file_id'
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOpen($query)
    {
        return $query->where('status_id', 2);
    }

    public function scopeDraft($query)
    {
        return $query->where('status_id', 1);
    }

    public function scopeClosed($query)
    {
        return $query->where('status_id', 3);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CallStatus::class, 'status_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function guidelineFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'guideline_file_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }
}
