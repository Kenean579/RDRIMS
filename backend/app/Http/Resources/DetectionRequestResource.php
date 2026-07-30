<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetectionRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'detectable' => [
                'type' => $this->detectable_type,
                'id' => $this->detectable_id,
            ],
            'file' => [
                'id' => $this->file_id,
                'name' => $this->file?->file_name,
            ],
            'service' => [
                'id' => $this->service_id,
                'name' => $this->service?->name,
            ],
            'status' => [
                'id' => $this->status_id,
                'name' => $this->status?->name,
            ],
            'requested_by' => [
                'id' => $this->requested_by,
                'name' => $this->requestedBy?->name,
                'email' => $this->requestedBy?->email,
            ],
            'results' => DetectionResultResource::collection($this->whenLoaded('results')),
            'completed_by' => $this->when($this->completed_by !== null, [
                'id' => $this->completed_by,
                'name' => $this->completedBy?->name,
            ]),
            'reviewed_by' => $this->when($this->reviewed_by !== null, [
                'id' => $this->reviewed_by,
                'name' => $this->reviewedBy?->name,
            ]),
            // Computed properties
            'is_pending' => $this->isPending(),
            'is_processing' => $this->isProcessing(),
            'is_completed' => $this->isCompleted(),
            'is_failed' => $this->isFailed(),
            'is_reviewed' => $this->isReviewed(),
            'can_retry' => $this->canRetry(),
            'is_immutable' => $this->isImmutable(),
            // Timestamps
            'requested_at' => $this->requested_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
