<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'proposal_id' => $this->proposal_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'total_budget' => $this->total_budget,
            'budget_allocation' => $this->budget_allocation,
            'status' => [
                'id' => $this->status_id,
                'name' => $this->status?->name,
            ],
            'pi' => [
                'id' => $this->pi_id,
                'name' => $this->pi?->name,
                'email' => $this->pi?->email,
            ],
            'academic_year' => [
                'id' => $this->academic_year_id,
                'name' => $this->academicYear?->name,
            ],
            'research_center' => $this->when($this->research_center_id, [
                'id' => $this->research_center_id,
                'name' => $this->researchCenter?->name,
            ]),
            'cover_image' => $this->when($this->cover_image_id, [
                'id' => $this->cover_image_id,
                'path' => $this->coverImage?->path,
                'url' => $this->coverImage?->url,
            ]),
            
            // Relationships
            'investigators' => ProjectInvestigatorResource::collection($this->whenLoaded('investigators')),
            'milestones' => MilestoneResource::collection($this->whenLoaded('milestones')),
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
            'publications' => $this->whenLoaded('publications', fn() => $this->publications),
            'patents' => $this->whenLoaded('patents', fn() => $this->patents),
            'outputs' => $this->whenLoaded('outputs', fn() => $this->outputs),
            'fundings' => $this->whenLoaded('fundings', fn() => $this->fundings),
            'histories' => $this->whenLoaded('histories', fn() => $this->histories),
            
            // Calculated fields
            'progress_percentage' => $this->when($request->include_stats, fn() => $this->getProgressPercentage()),
            'remaining_budget' => $this->when($request->include_stats, fn() => $this->getRemainingBudget()),
            'is_overdue' => $this->when($request->include_stats, fn() => $this->isOverdue()),
            'can_complete' => $this->when($request->include_stats, fn() => $this->canComplete()),
            
            // Audit fields
            'created_by' => [
                'id' => $this->created_by,
                'name' => $this->whenLoaded('createdBy', fn() => $this->createdBy?->name),
            ],
            'updated_by' => $this->when($this->updated_by, [
                'id' => $this->updated_by,
                'name' => $this->whenLoaded('updatedBy', fn() => $this->updatedBy?->name),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at?->toIso8601String()),
        ];
    }
}
