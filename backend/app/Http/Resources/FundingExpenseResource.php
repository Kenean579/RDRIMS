<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundingExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'funding_id' => $this->funding_id,
            'reference_number' => $this->reference_number,
            'description' => $this->description,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'expense_date' => $this->expense_date?->toISOString(),
            'status' => $this->status,

            'budget_category' => $this->whenLoaded('budgetCategory', fn() => [
                'id' => $this->budgetCategory->id,
                'name' => $this->budgetCategory->name,
            ]),

            'expense_category' => $this->whenLoaded('expenseCategory', fn() => [
                'id' => $this->expenseCategory->id,
                'name' => $this->expenseCategory->name,
            ]),

            'submitted_by' => $this->whenLoaded('submittedBy', fn() => [
                'id' => $this->submittedBy->id,
                'name' => $this->submittedBy->name,
            ]),

            'approved_by' => $this->whenLoaded('approvedBy', fn() => [
                'id' => $this->approvedBy?->id,
                'name' => $this->approvedBy?->name,
            ]),

            'approved_at' => $this->approved_at?->toISOString(),
            'approval_notes' => $this->approval_notes,

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
