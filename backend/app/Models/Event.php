<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory, \App\Traits\HasDynamicStatus;

    protected $fillable = [
        'title', 'start_date', 'end_date', 'location', 'description',
        'max_participants', 'status_id', 'organizer_id', 'registration_deadline',
        'banner_id'
    ];

    protected $casts = [
        'start_date'            => 'datetime',
        'end_date'              => 'datetime',
        'registration_deadline' => 'datetime',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(EventStatus::class, 'status_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(File::class, 'banner_id');
    }
}
