<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationAuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'publication_id' => $this->publication_id,
            'user_id' => $this->user_id,
            'external_author_name' => $this->external_author_name,
            'external_institution' => $this->external_institution,
            'author_order' => $this->author_order,
            'contribution_role' => $this->contribution_role,
            'is_corresponding' => $this->is_corresponding,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            
            // Computed attributes
            'display_name' => $this->display_name,
            'is_internal' => $this->isInternal(),
            'is_first_author' => $this->isFirstAuthor(),
        ];
    }
}
