<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundingAllocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'funding_id' => $this->funding_id,
            'budget_category' => $this->whenLoaded('budgetCategory', fn() => [
                'id' => $this->budgetCategory->id,
                'name' => $this->budgetCategory->name,
            ]),
            'allocated_amount' => $this->allocated_amount,
            'used_amount' => $this->used_amount,
            'revised_amount' => $this->revised_amount,
            'remaining_budget' => $this->getRemainingBudget(),
            'utilization_percent' => $this->getUtilizationPercentage(),
            'revision_approved_by' => $this->revision_approved_by,
            'revision_approved_at' => $this->revision_approved_at?->toISOString(),
            'revision_notes' => $this->revision_notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
