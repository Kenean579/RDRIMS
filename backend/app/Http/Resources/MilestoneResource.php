<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MilestoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'display_order' => $this->display_order,
            'status' => [
                'id' => $this->status_id,
                'name' => $this->status?->name,
            ],
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'tasks_count' => $this->when($request->include_stats, fn() => $this->tasks()->count()),
            'completed_tasks_count' => $this->when($request->include_stats, fn() => 
                $this->tasks()->whereHas('status', fn($q) => $q->where('name', 'completed'))->count()
            ),
            'is_overdue' => $this->when($request->include_stats, fn() => 
                $this->due_date < now() && $this->status?->name !== 'completed'
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
