<?php

namespace App\Services;

use App\Models\EthicsRequest;
use App\Models\Proposal;
use App\Models\User;

class EthicsService
{
    public function submitRequest(Proposal $proposal, array $data, User $user): EthicsRequest
    {
        return $proposal->ethicsRequest()->create([
            'generated_pdf_path' => $data['pdf_path'] ?? 'temp_path',
            'submitted_to_irb' => true,
            'approval_status_id' => EthicsRequest::getStatusId('pending'),
            'comments' => $data['comments'] ?? null,
            'version' => $data['version'] ?? 1,
        ]);
    }

    public function approve(EthicsRequest $request, User $approvedBy, ?string $comment = null): void
    {
        $request->update([
            'approval_status_id' => EthicsRequest::getStatusId('approved'),
            'comments' => $comment,
        ]);
    }

    public function reject(EthicsRequest $request, User $rejectedBy, string $comment): void
    {
        $request->update([
            'approval_status_id' => EthicsRequest::getStatusId('rejected'),
            'comments' => $comment,
        ]);
    }
}
