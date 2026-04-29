<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DetectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'detectable_type', 'detectable_id', 'file_id',
        'service_id', 'status_id', 'requested_by', 'requested_at', 'completed_at'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function detectable(): MorphTo
    {
        return $this->morphTo();
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(DetectionService::class, 'service_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(DetectionStatus::class, 'status_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(DetectionResult::class);
    }
}
