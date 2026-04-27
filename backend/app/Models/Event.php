<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'venue',
        'description',
        'capacity',
        'registration_deadline',
        'image_file_id'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'registration_deadline' => 'datetime',
            'capacity' => 'integer'
        ];
    }

    public function imageFile()
    {
        return $this->belongsTo(File::class, 'image_file_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }
}
