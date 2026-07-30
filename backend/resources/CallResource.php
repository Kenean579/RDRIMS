<?php
// app/Http/Resources/CallResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CallResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'deadline'        => $this->deadline?->toDateString(),
            'thematic_areas'  => $this->thematic_areas,
            'budget_limit'    => $this->metadata['budget_limit'] ?? null,

            // Status
            'status'          => $this->whenLoaded('status', fn() => [
                'id'   => $this->status->id,
                'name' => $this->status->name,
            ]),

            // Academic Year
            'academic_year'   => $this->whenLoaded('academicYear', fn() => [
                'id'   => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ]),

            // Creator
            'creator'         => $this->whenLoaded('createdBy', fn() => [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ]),

            // Guideline File
            'guideline_file'  => $this->whenLoaded('guidelineFile', fn() => [
                'id'   => $this->guidelineFile->id,
                'path' => $this->guidelineFile->file_path,
            ]),

            // Hierarchy (internal – only for admin views, filtered by resource)
            'university_id'       => $this->university_id,
            'campus_id'           => $this->campus_id,
            'faculty_id'          => $this->faculty_id,
            'department_id'       => $this->department_id,
            'research_center_id'  => $this->research_center_id,

            // Timestamps
            'created_at'      => $this->created_at->toISOString(),
            'updated_at'      => $this->updated_at->toISOString(),
            'published_at'    => $this->published_at?->toISOString(),
            'opens_at'        => $this->opens_at?->toISOString(),
            'closes_at'       => $this->closes_at?->toISOString(),

            // Counts
            'proposals_count' => $this->whenCounted('proposals', 0),

            // Public visibility
            'is_public'       => $this->is_public,
            'is_featured'     => $this->is_featured,
        ];
    }
}
