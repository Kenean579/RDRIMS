<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'inventors',
        'filing_date',
        'patent_number',
        'status_id'
    ];

    protected function casts(): array
    {
        return [
            'filing_date' => 'date'
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(PatentStatus::class, 'status_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function files()
    {
        return $this->belongsToMany(File::class, 'patent_files');
    }
}
