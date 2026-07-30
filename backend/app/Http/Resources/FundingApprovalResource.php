<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundingApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'funding_id' => $this->funding_id,
            'action' => $this->action,
            'approved_by' => $this->whenLoaded('approvedBy', fn() => [
                'id' => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ]),
            'approved_at' => $this->approved_at->toISOString(),
            'comments' => $this->comments,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
