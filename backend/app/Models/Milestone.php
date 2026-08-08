<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'description', 'due_date', 'display_order', 'status_id'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(MilestoneStatus::class, 'status_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function updateStatusFromTasks(): void
    {
        $tasks = $this->tasks()->with('status')->get();
        if ($tasks->isEmpty()) {
            $pendingStatus = \App\Models\MilestoneStatus::where('name', 'pending')->first();
            if ($pendingStatus && $this->status_id !== $pendingStatus->id) {
                $this->status_id = $pendingStatus->id;
                $this->save();
            }
            return;
        }

        $allDone = true;
        $anyStarted = false;

        foreach ($tasks as $task) {
            $statusName = $task->status?->name;
            if ($statusName === 'done') {
                $anyStarted = true;
            } elseif ($statusName === 'in_progress') {
                $anyStarted = true;
                $allDone = false;
            } else {
                $allDone = false;
            }
        }

        $newStatusName = 'pending';
        if ($allDone) {
            $newStatusName = \App\Models\MilestoneStatus::whereIn('name', ['completed', 'done'])->first()?->name ?? 'done';
        } elseif ($anyStarted) {
            $newStatusName = \App\Models\MilestoneStatus::where('name', 'in_progress')->first()?->name ?? 'pending';
        } else {
            $newStatusName = 'pending';
        }

        $status = \App\Models\MilestoneStatus::where('name', $newStatusName)->first();
        if ($status && $this->status_id !== $status->id) {
            $this->status_id = $status->id;
            $this->save();
        }
    }
}
