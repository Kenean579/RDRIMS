<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalInvestigator extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id', 'user_id', 'name', 'email', 'institution',
        'role_id', 'status_id', 'invited_at'
    ];

    protected $casts = [
        'invited_at' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(InvestigatorRole::class, 'role_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(InvitationStatus::class, 'status_id');
    }
}
