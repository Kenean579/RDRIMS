<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'deadline', 'thematic_areas',
        'created_by', 'status_id', 'academic_year_id', 'guideline_file_id'
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function createdBy(): BelongsTo
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
