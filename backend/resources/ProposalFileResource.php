<?php
// app/Http/Resources/ProposalFileResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProposalFileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'file_path'    => $this->file_path,
            'version'      => $this->version,
            'category'     => $this->pivot->category ?? 'general',
            'uploaded_by'  => $this->uploadedBy?->name,
            'is_public'    => $this->is_public,
            'download_url' => route('files.download', $this->id),
            'created_at'   => $this->created_at->toISOString(),
        ];
    }
}
