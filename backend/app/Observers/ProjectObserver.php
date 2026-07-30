<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\ProjectHistory;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        ProjectHistory::create([
            'project_id' => $project->id,
            'action' => 'created',
            'performed_by' => $project->created_by ?? auth()->id(),
            'description' => "Project '{$project->title}' created",
        ]);
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        if ($project->wasChanged() && !$project->wasChanged(['updated_at', 'updated_by'])) {
            $changes = $project->getChanges();
            
            ProjectHistory::create([
                'project_id' => $project->id,
                'action' => 'updated',
                'performed_by' => $project->updated_by ?? auth()->id(),
                'description' => "Project '{$project->title}' updated",
                'changes' => $changes,
            ]);
        }
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        ProjectHistory::create([
            'project_id' => $project->id,
            'action' => 'deleted',
            'performed_by' => auth()->id(),
            'description' => "Project '{$project->title}' deleted",
        ]);
    }
}
