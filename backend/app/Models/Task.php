<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_id',
        'title',
        'description',
        'estimated_hours',
        'actual_hours',
        'assigned_to',
        'due_date',
        'status_id'
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'estimated_hours' => 'integer',
            'actual_hours' => 'integer'
        ];
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }
}
