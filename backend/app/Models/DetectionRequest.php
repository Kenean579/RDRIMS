<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class DetectionRequest extends Model
{
    use HasFactory, \App\Traits\HasDynamicStatus, \App\Traits\HierarchicalScope, SoftDeletes;

    protected $fillable = [
        'detectable_type', 'detectable_id', 'file_id',
        'service_id', 'status_id', 'requested_at',
        'requested_by', 'completed_by', 'completed_at',
        'reviewed_by', 'reviewed_at', 'deleted_by'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

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

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(DetectionResult::class);
    }

    /**
     * Status check methods
     */
    public function isPending(): bool
    {
        return $this->status_id === self::getStatusId('pending');
    }

    public function isProcessing(): bool
    {
        return $this->status_id === self::getStatusId('processing');
    }

    public function isCompleted(): bool
    {
        return $this->status_id === self::getStatusId('completed');
    }

    public function isFailed(): bool
    {
        return $this->status_id === self::getStatusId('failed');
    }

    public function isReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    /**
     * Workflow methods
     */
    public function canRetry(): bool
    {
        // Can retry if failed
        if ($this->isFailed()) {
            return true;
        }
        
        // Can retry if pending and hasn't exceeded attempt limit
        if ($this->isPending()) {
            $attemptCount = $this->attributes['attempts_count'] ?? 0;
            return $attemptCount < 3;
        }
        
        return false;
    }

    public function isImmutable(): bool
    {
        // Request is immutable if reviewed
        return $this->isReviewed();
    }

    /**
     * Scope methods
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status_id', self::getStatusId('pending'));
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status_id', self::getStatusId('processing'));
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status_id', self::getStatusId('completed'));
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status_id', self::getStatusId('failed'));
    }

    public function scopeReviewed(Builder $query): Builder
    {
        return $query->whereNotNull('reviewed_at');
    }

    public function scopeByService(Builder $query, int $serviceId): Builder
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('requested_by', $userId);
    }
}
