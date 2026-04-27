<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'detectable_type', 'detectable_id', 'file_id', 'service_id',
        'status_id', 'requested_by', 'requested_at', 'completed_at'
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function detectable()
    {
        return $this->morphTo();
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function service()
    {
        return $this->belongsTo(DetectionService::class, 'service_id');
    }

    public function status()
    {
        return $this->belongsTo(DetectionStatus::class, 'status_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function results()
    {
        return $this->hasMany(DetectionResult::class);
    }
}
