<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'due_date',
        'display_order',
        'status_id'
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'display_order' => 'integer'
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(MilestoneStatus::class, 'status_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
