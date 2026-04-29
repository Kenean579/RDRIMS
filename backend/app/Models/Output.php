<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Output extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'student_level_id', 'subtype_id', 'proposal_id',
        'title', 'abstract', 'partner_id', 'project_id', 'status_id',
        'start_date', 'end_date', 'feedback', 'academic_year_id', 'budget'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'budget'     => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(OutputCategory::class, 'category_id');
    }

    public function studentLevel(): BelongsTo
    {
        return $this->belongsTo(StudentLevel::class, 'student_level_id');
    }

    public function subtype(): BelongsTo
    {
        return $this->belongsTo(OutputSubtype::class, 'subtype_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OutputStatus::class, 'status_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(OutputParticipant::class);
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'output_files')
                    ->withTimestamps()
                    ->using(OutputFile::class);
    }
}
