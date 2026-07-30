<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetectionResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'detection_request_id', 'similarity_score', 'ai_probability',
        'report_file_id', 'raw_response'
    ];

    protected $guarded = [
        'deleted_by', 'deleted_at'
    ];

    protected $casts = [
        'raw_response' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function detectionRequest(): BelongsTo
    {
        return $this->belongsTo(DetectionRequest::class);
    }

    public function reportFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'report_file_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
