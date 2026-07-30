<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetectionResultResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'similarity_score' => $this->similarity_score,
            'ai_probability' => $this->ai_probability,
            'report_file' => $this->when($this->report_file_id !== null, [
                'id' => $this->report_file_id,
                'name' => $this->reportFile?->file_name,
                'path' => $this->reportFile?->file_path,
            ]),
            'raw_response' => $this->raw_response,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}