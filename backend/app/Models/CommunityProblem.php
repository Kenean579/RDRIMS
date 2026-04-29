<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityProblem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'location', 'submitted_by',
        'contact_info', 'status_id', 'claimed_by', 'claimed_at',
        'completed_at', 'linked_project_id', 'feedback', 'rating', 'is_anonymous'
    ];

    protected $casts = [
        'claimed_at'   => 'datetime',
        'completed_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'rating'       => 'integer',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(CommunityProblemStatus::class, 'status_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function linkedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'linked_project_id');
    }
}
