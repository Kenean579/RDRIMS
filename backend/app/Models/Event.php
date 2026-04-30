<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'start_date', 'end_date', 'venue', 'description',
        'capacity', 'registration_deadline', 'image_file_id'
    ];

    protected $casts = [
        'start_date'            => 'datetime',
        'end_date'              => 'datetime',
        'registration_deadline' => 'datetime',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function imageFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_file_id');
    }
}
