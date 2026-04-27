<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    // No updated_at as per schema
    const UPDATED_AT = null;

    protected $fillable = [
        'file_path',
        'version',
        'uploaded_by',
        'is_public'
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'version' => 'integer'
        ];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
