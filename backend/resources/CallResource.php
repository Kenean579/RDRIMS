

<?php
// app/Http/Resources/CallResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Call model into a consistent JSON API response.
 */
class CallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'deadline'          => $this->deadline->format('Y-m-d'),
            'thematic_areas'    => $this->thematic_areas,
            'status'            => [
                'id'   => $this->status_id,
                'name' => $this->status?->name,
            ],
            'academic_year'     => $this->whenLoaded('academicYear', function () {
                return [
                    'id'   => $this->academicYear->id,
                    'name' => $this->academicYear->name,
                ];
            }),
            'guideline_file'    => $this->whenLoaded('guidelineFile', function () {
                return [
                    'id'          => $this->guidelineFile->id,
                    'file_path'   => $this->guidelineFile->file_path,
                    'download_url' => route('files.download', $this->guidelineFile->id),
                ];
            }),
            'creator'           => [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'proposals_count'   => $this->whenCounted('proposals', $this->proposals_count ?? $this->proposals->count()),
            'created_at'        => $this->created_at->toISOString(),
            'updated_at'        => $this->updated_at->toISOString(),
        ];
    }
}
