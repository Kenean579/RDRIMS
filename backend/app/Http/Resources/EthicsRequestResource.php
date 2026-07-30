<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EthicsRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'proposal_id'          => $this->proposal_id,
            'version'              => $this->version,
            'generated_pdf_path'   => $this->generated_pdf_path,
            'comments'             => $this->comments,
            'submitted_to_irb'     => $this->submitted_to_irb,

            'approval_status' => [
                'id'   => $this->approval_status_id,
                'name' => $this->approvalStatus?->name,
            ],

            'proposal' => $this->whenLoaded('proposal', fn() => [
                'id'                => $this->proposal->id,
                'title'             => $this->proposal->title,
                'abstract'          => $this->proposal->abstract,
                'status_id'         => $this->proposal->status_id,
                'status_name'       => $this->proposal->status?->name,
            ]),

            'reviewer' => $this->whenLoaded('reviewer', fn() => [
                'id'   => $this->reviewer->id,
                'name' => $this->reviewer->name,
                'email' => $this->reviewer->email,
            ]),

            'created_by_user' => $this->whenLoaded('createdBy', fn() => [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email,
            ]),

            'updated_by_user' => $this->whenLoaded('updatedBy', fn() => [
                'id'   => $this->updatedBy->id,
                'name' => $this->updatedBy->name,
                'email' => $this->updatedBy->email,
            ]),

            'reviewed_at'  => $this->reviewed_at?->toISOString(),
            'created_at'   => $this->created_at->toISOString(),
            'updated_at'   => $this->updated_at->toISOString(),
            'deleted_at'   => $this->deleted_at?->toISOString(),

            // Computed states
            'is_pending'       => $this->isPending(),
            'is_approved'      => $this->isApproved(),
            'is_rejected'      => $this->isRejected(),
            'needs_revision'   => $this->needsRevision(),
            'is_reviewed'      => $this->isReviewed(),
            'can_edit'         => $this->canEdit(),
            'can_decide'       => $this->canDecide(),
        ];
    }
}
