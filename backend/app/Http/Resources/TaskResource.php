<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'milestone_id' => $this->milestone_id,
            'title' => $this->title,
            'description' => $this->description,
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => [
                'id' => $this->status_id,
                'name' => $this->status?->name,
            ],
            'assigned_to' => $this->when($this->assigned_to, [
                'id' => $this->assigned_to,
                'name' => $this->assignedTo?->name,
                'email' => $this->assignedTo?->email,
            ]),
            'is_overdue' => $this->when($request->include_stats, fn() => 
                $this->due_date && $this->due_date < now() && $this->status?->name !== 'completed'
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
