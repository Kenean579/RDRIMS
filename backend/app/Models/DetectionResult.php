<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'detection_request_id', 'similarity_score', 'ai_probability',
        'report_file_id', 'raw_response'
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function detectionRequest(): BelongsTo
    {
        return $this->belongsTo(DetectionRequest::class);
    }

    public function reportFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'report_file_id');
    }
}
