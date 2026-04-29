<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Pivot
{
    use HasFactory;

    protected $fillable = ['user_id', 'role_id', 'assigned_by', 'assigned_at'];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
