<?php
// app/Http/Resources/ReviewCriterionResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewCriterionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'weight'      => $this->weight,
            'call_id'     => $this->call_id,
            'is_active'   => $this->is_active,
            'created_at'  => $this->created_at->toISOString(),
            'updated_at'  => $this->updated_at->toISOString(),
        ];
    }
}
