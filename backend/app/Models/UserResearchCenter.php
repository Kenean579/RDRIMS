<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserResearchCenter extends Pivot
{
    use HasFactory;

    protected $fillable = ['user_id', 'research_center_id', 'center_role_id'];

    public function centerRole(): BelongsTo
    {
        return $this->belongsTo(CenterRole::class, 'center_role_id');
    }
}
