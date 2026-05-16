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
            'request_date' => now(),
            'status' => 'pending',
            'submitted_by' => $user->id,
            'details' => $data['details'] ?? null,
        ]);
    }

    public function approve(EthicsRequest $request, User $approvedBy, ?string $comment = null): void
    {
        $request->update([
            'status' => 'approved',
            'approved_by' => $approvedBy->id,
            'approval_date' => now(),
            'comments' => $comment,
        ]);
    }

    public function reject(EthicsRequest $request, User $rejectedBy, string $comment): void
    {
        $request->update([
            'status' => 'rejected',
            'comments' => $comment,
        ]);
    }
}
