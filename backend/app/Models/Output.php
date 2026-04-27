<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Output extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'student_level_id',
        'subtype_id',
        'proposal_id',
        'title',
        'abstract',
        'partner_id',
        'project_id',
        'status_id',
        'start_date',
        'end_date',
        'feedback',
        'academic_year_id',
        'budget'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2'
        ];
    }

    public function category()
    {
        return $this->belongsTo(OutputCategory::class, 'category_id');
    }

    public function studentLevel()
    {
        return $this->belongsTo(StudentLevel::class, 'student_level_id');
    }

    public function subtype()
    {
        return $this->belongsTo(OutputSubtype::class, 'subtype_id');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(OutputStatus::class, 'status_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'output_participants')
                    ->withPivot('participant_type_id');
    }

    public function files()
    {
        return $this->belongsToMany(File::class, 'output_files');
    }
}
