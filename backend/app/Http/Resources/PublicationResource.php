<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'title' => $this->title,
            'abstract' => $this->abstract,
            'keywords' => $this->keywords,
            'journal' => $this->journal,
            'volume' => $this->volume,
            'issue' => $this->issue,
            'pages' => $this->pages,
            'publisher' => $this->publisher,
            'conference_name' => $this->conference_name,
            'doi' => $this->doi,
            'isbn' => $this->isbn,
            'issn' => $this->issn,
            'scholar_url' => $this->scholar_url,
            'publication_date' => $this->publication_date?->format('Y-m-d'),
            'citation_count' => $this->citation_count,
            'file_id' => $this->file_id,
            'research_center_id' => $this->research_center_id,
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'project' => $this->whenLoaded('project', fn() => $this->project ? [
                'id' => $this->project->id,
                'title' => $this->project->title,
            ] : null),
            'status' => $this->whenLoaded('status', fn() => $this->status ? [
                'id' => $this->status->id,
                'name' => $this->status->name,
            ] : null),
            'type' => $this->whenLoaded('type', fn() => $this->type ? [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ] : null),
            'authors' => PublicationAuthorResource::collection($this->whenLoaded('authors')),
            'file' => $this->whenLoaded('file', fn() => [
                'id' => $this->file->id,
                'name' => $this->file->name,
                'path' => $this->file->path,
            ]),
            'research_center' => $this->whenLoaded('researchCenter', fn() => $this->researchCenter ? [
                'id' => $this->researchCenter->id,
                'name' => $this->researchCenter->name,
            ] : null),
            'created_by' => $this->whenLoaded('createdBy', fn() => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ] : null),
            'verified_by' => $this->whenLoaded('verifiedBy', fn() => $this->verifiedBy ? [
                'id' => $this->verifiedBy->id,
                'name' => $this->verifiedBy->name,
            ] : null),
            
            // Computed attributes
            'author_names' => $this->when($this->relationLoaded('authors'), $this->author_names),
            'is_verified' => $this->isVerified(),
            'is_published' => $this->isPublished(),
            'can_submit' => $this->canSubmit(),
            'can_publish' => $this->canPublish(),
        ];
    }
}
