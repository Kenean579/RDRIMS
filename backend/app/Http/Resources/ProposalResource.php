<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'abstract'              => $this->abstract,
            'objectives'            => $this->objectives,
            'methodology'           => $this->methodology,
            'keywords'              => $this->keywords,
            'budget'                => $this->budget,
            'budget_allocation'     => $this->budget_allocation,
            'status_change_comment' => $this->status_change_comment,

            'type' => [
                'id'   => $this->type_id,
                'name' => $this->type?->name,
            ],
            'status' => [
                'id'   => $this->status_id,
                'name' => $this->status?->name,
            ],
            'call' => $this->whenLoaded('call', fn() => [
                'id'    => $this->call->id,
                'title' => $this->call->title,
            ]),
            'submitted_by' => [
                'id'   => $this->submitted_by,
                'name' => $this->submittedBy?->name,
            ],
            'approved_by' => $this->whenLoaded('approvedBy', fn() => [
                'id'   => $this->approved_by,
                'name' => $this->approvedBy->name,
            ]),
            'academic_year' => $this->whenLoaded('academicYear', fn() => [
                'id'   => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ]),
            'file' => $this->whenLoaded('file', fn() => [
                 'id'   => $this->file->id,
    'name' => $this->file->original_filename,
    'url'  => route('files.download', $this->file->id),
            ]),
            'ethics_file' => $this->whenLoaded('ethicsFile', fn() => [
                 'id'   => $this->ethicsFile->id,
    'name' => $this->ethicsFile->original_name,
    'url'  => route('files.download', $this->ethicsFile->id),
    'url'  => route('files.download', $this->file->id),
            ]),
            'ethics_approval_status' => $this->whenLoaded('ethicsApprovalStatus', fn() => [
                'id'   => $this->ethics_approval_status_id,
                'name' => $this->ethicsApprovalStatus->name,
            ]),
            'investigators' => $this->whenLoaded('investigators'),
            'reviewers'     => $this->whenLoaded('reviewers', function() {
                // SECURITY: Use ProposalReviewerResource to control data exposure
                return \App\Http\Resources\ProposalReviewerResource::collection($this->reviewers);
            }),
            'finance_checks'=> $this->whenLoaded('financeChecks'),
            'ethics_requests'=> $this->whenLoaded('ethicsRequests'),
            'detection'     => $this->whenLoaded('detectionRequests'),
            'project'       => $this->whenLoaded('project', fn() => ['id' => $this->project->id]),
            'ethics_status' => $this->relationLoaded('ethicsRequests')
                ? ($this->ethicsRequests->sortByDesc('created_at')->first()?->approvalStatus?->name ?? 'not_requested')
                : 'not_requested',
            'finance_status' => $this->relationLoaded('financeChecks')
                ? ($this->financeChecks->sortByDesc('created_at')->first()?->status?->name ?? 'pending')
                : 'pending',
            'submitted_at'  => $this->submitted_at?->toISOString(),
            'approved_at'   => $this->approved_at?->toISOString(),
            'created_at'    => $this->created_at->toISOString(),
            'updated_at'    => $this->updated_at->toISOString(),

            // REVIEWER: Add dynamic attributes if set by controller
            'reviewPivot' => $this->when(isset($this->reviewPivot), $this->reviewPivot),
            'is_locked' => $this->when(isset($this->is_locked), $this->is_locked),
        ];
    }
}
