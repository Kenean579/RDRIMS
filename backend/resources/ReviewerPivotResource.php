<?php
// app/Http/Resources/ReviewerPivotResource.php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ReviewerPivotResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'reviewer' => [
                'id'   => $this->id,
                'name' => $this->name,
            ],
            'assigned_by'   => $this->reviewPivot->assigned_by,
            'assigned_at'   => $this->reviewPivot->assigned_at?->toISOString(),
            'submitted_at'  => $this->reviewPivot->submitted_at?->toISOString(),
            'overall_score' => $this->reviewPivot->overall_score,
            'overall_comments' => $this->reviewPivot->overall_comments,
            'decision'      => $this->reviewPivot->decision?->name,
        ];
    }
}
