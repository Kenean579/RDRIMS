<?php
// app/Http/Resources/EthicsRequestResource.php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class EthicsRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'submitted_to_irb' => $this->submitted_to_irb,
            'approval_status'  => $this->approvalStatus?->name,
            'comments'     => $this->comments,
            'version'      => $this->version,
            'pdf_url'      => $this->generated_pdf_path ? asset('storage/' . $this->generated_pdf_path) : null,
            'created_at'   => $this->created_at->toISOString(),
        ];
    }
}
