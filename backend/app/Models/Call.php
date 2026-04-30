<?php
// app/Models/Call.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'title',
        'description',
        'deadline',
        'thematic_areas',
        'created_by',
        'status_id',
        'academic_year_id',
        'guideline_file_id',
    ];

    /**
     * The attributes that should be cast.
     */
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

    /**
     * Scope a query to only include open calls.
     * Assumes call_statuses: 1=draft, 2=open, 3=closed.
     */
    public function scopeOpen($query)
    {
        return $query->where('status_id', 2);
    }

    /**
     * Scope a query to only include draft calls.
     */
    public function scopeDraft($query)
    {
        return $query->where('status_id', 1);
    }

    /**
     * Scope a query to only include closed calls.
     */
    public function scopeClosed($query)
    {
        return $query->where('status_id', 3);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user who created this call.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the status of this call.
     */
    public function status()
    {
        return $this->belongsTo(CallStatus::class, 'status_id');
    }

    /**
     * Get the academic year associated with this call.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the guideline file (PDF) for this call.
     */
    public function guidelineFile()
    {
        return $this->belongsTo(File::class, 'guideline_file_id');
    }

    /**
     * Get all proposals submitted to this call.
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }
}
