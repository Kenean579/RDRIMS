<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'generated_by',
        'generated_at',
        'parameters'
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'parameters' => 'json'
        ];
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
