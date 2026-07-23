<?php
// app/Http/Resources/CallResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CallResource
 * 
 * Transforms Call models into JSON API responses.
 * Exposes only public business data - never exposes sensitive tenant fields.
 * 
 * Excluded Fields (Security):
 * - university_id, campus_id, faculty_id, department_id, research_center_id (tenant structure)
 * - created_by (use creator object instead)
 * - is_featured (internal flag)
 * - metadata (internal data)
 * - is_public (redundant in response)
 * - published_at, opens_at, closes_at (redundant with visibility logic)
 * - deleted_at (soft delete state)
 * 
 * Exposed Fields (Public Business Data):
 * - id, title, description, deadline, thematic_areas
 * - status (id, name)
 * - academic_year (id, name)
 * - guideline_file (id, file_path, download_url - with access control)
 * - creator (id, name)
 * - proposals_count (anonymized count)
 * - timestamps
 */
class CallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * Only exposes public business information.
     * Tenant structure and internal fields are intentionally excluded.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'deadline'          => $this->deadline->format('Y-m-d'),
            'thematic_areas'    => $this->thematic_areas,
            
            // Status information
            'status'            => [
                'id'   => $this->status_id,
                'name' => $this->status?->name,
            ],
            
            // Academic year (global reference, no tenant data)
            'academic_year'     => $this->whenLoaded('academicYear', function () {
                return [
                    'id'   => $this->academicYear->id,
                    'name' => $this->academicYear->name,
                ];
            }),
            
            // Guideline file with download link
            // File access is controlled by FileController policy
            'guideline_file'    => $this->whenLoaded('guidelineFile', function () {
                return [
                    'id'          => $this->guidelineFile->id,
                    'file_path'   => $this->guidelineFile->file_path,
                    'download_url' => route('files.download', $this->guidelineFile->id),
                ];
            }),
            
            // Creator information (no internal IDs)
            'creator'           => [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ],
            
            // Proposal count (anonymized, no individual identifiers)
            'proposals_count'   => $this->whenCounted('proposals', $this->proposals_count ?? $this->proposals->count()),
            
            // Timestamps
            'created_at'        => $this->created_at->toISOString(),
            'updated_at'        => $this->updated_at->toISOString(),
        ];
    }
}
