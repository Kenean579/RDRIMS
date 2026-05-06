<?php
// app/Http/Resources/FinanceCheckResource.php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class FinanceCheckResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'status'      => $this->status?->name,
            'comments'    => $this->comments,
            'checked_by'  => $this->checker?->name,
            'checked_at'  => $this->checked_at?->toISOString(),
        ];
    }
}
