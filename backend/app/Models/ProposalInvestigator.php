<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalInvestigator extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id', 'user_id', 'name', 'email', 'institution',
        'role_id', 'status_id', 'invited_at'
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
        ];
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(InvestigatorRole::class, 'role_id');
    }

    public function status()
    {
        return $this->belongsTo(InvitationStatus::class, 'status_id');
    }
}
