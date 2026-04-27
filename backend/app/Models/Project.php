<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'title',
        'start_date',
        'end_date',
        'total_budget',
        'budget_allocation',
        'cover_image_id',
        'status_id',
        'pi_id',
        'academic_year_id'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_budget' => 'decimal:2',
            'budget_allocation' => 'json'
        ];
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function pi()
    {
        return $this->belongsTo(User::class, 'pi_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function coverImage()
    {
        return $this->belongsTo(File::class, 'cover_image_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function publications()
    {
        return $this->hasMany(Publication::class);
    }

    public function patents()
    {
        return $this->hasMany(Patent::class);
    }

    public function files()
    {
        return $this->belongsToMany(File::class, 'project_files');
    }
}
