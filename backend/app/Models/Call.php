<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function status()
    {
        return $this->belongsTo(CallStatus::class, 'status_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function guidelineFile()
    {
        return $this->belongsTo(File::class, 'guideline_file_id');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }
}
