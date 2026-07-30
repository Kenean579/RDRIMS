<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'title' => $this->title,
            'description' => $this->description,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'is_internal' => $this->is_internal,

            'status' => $this->whenLoaded('status', fn() => [
                'id' => $this->status->id,
                'name' => $this->status->name,
            ]),

            'funding_source' => $this->whenLoaded('fundingSource', fn() => [
                'id' => $this->fundingSource->id,
                'name' => $this->fundingSource->name,
                'type' => $this->fundingSource->type,
            ]),

            'project' => $this->whenLoaded('project', fn() => [
                'id' => $this->project?->id,
                'title' => $this->project?->title,
            ]),

            'proposal' => $this->whenLoaded('proposal', fn() => [
                'id' => $this->proposal?->id,
                'title' => $this->proposal?->title,
            ]),

            'created_by' => [
                'id' => $this->created_by,
                'name' => $this->whenLoaded('createdBy', fn() => $this->createdBy->name),
            ],

            'approved_by' => $this->whenLoaded('approvedBy', fn() => [
                'id' => $this->approvedBy?->id,
                'name' => $this->approvedBy?->name,
            ]),

            'approved_at' => $this->approved_at?->toISOString(),

            'allocations' => $this->whenLoaded('allocations', fn() => 
                FundingAllocationResource::collection($this->allocations)
            ),

            'expenses' => $this->whenLoaded('expenses', fn() => 
                FundingExpenseResource::collection($this->expenses)
            ),

            'approvals' => $this->whenLoaded('approvals', fn() => 
                FundingApprovalResource::collection($this->approvals)
            ),

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
