<?php
// app/Http/Resources/InvestigatorResource.php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class InvestigatorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'name'          => $this->name ?? $this->user?->name,
            'email'         => $this->email ?? $this->user?->email,
            'institution'   => $this->institution,
            'role'          => $this->role?->name,
            'invitation_status' => $this->invitationStatus?->name,
            'invited_at'    => $this->invited_at?->toISOString(),
        ];
    }
}
