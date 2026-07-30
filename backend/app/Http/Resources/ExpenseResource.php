<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'amount' => $this->amount,
            'budget_category' => $this->budget_category,
            'description' => $this->description,
            'approved_by' => $this->when($this->approved_by, [
                'id' => $this->approved_by,
                'name' => $this->approvedBy?->name,
            ]),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'is_approved' => $this->approved_by !== null,
            'research_center' => $this->when($this->research_center_id, [
                'id' => $this->research_center_id,
                'name' => $this->researchCenter?->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
