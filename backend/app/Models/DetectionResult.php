<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetectionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'detection_request_id', 'similarity_score', 'ai_probability',
        'report_file_id', 'raw_response'
    ];

    protected function casts(): array
    {
        return [
            'raw_response' => 'json',
        ];
    }

    public function detectionRequest()
    {
        return $this->belongsTo(DetectionRequest::class);
    }

    public function reportFile()
    {
        return $this->belongsTo(File::class, 'report_file_id');
    }
}
