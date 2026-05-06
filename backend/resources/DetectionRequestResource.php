<?php
// app/Http/Resources/DetectionRequestResource.php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class DetectionRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'service'         => $this->service?->name,
            'status'          => $this->status?->name,
            'similarity_score'=> $this->result?->similarity_score,
            'ai_probability'  => $this->result?->ai_probability,
            'report_url'      => $this->result?->reportFile ? route('files.download', $this->result->reportFile->id) : null,
            'requested_at'    => $this->requested_at?->toISOString(),
            'completed_at'    => $this->completed_at?->toISOString(),
        ];
    }
}
