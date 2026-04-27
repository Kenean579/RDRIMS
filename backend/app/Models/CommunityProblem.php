<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityProblem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'submitted_by',
        'contact_info',
        'status_id',
        'claimed_by',
        'claimed_at',
        'completed_at',
        'linked_project_id',
        'feedback',
        'rating',
        'is_anonymous'
    ];

    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'completed_at' => 'datetime',
            'rating' => 'integer',
            'is_anonymous' => 'boolean'
        ];
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function status()
    {
        return $this->belongsTo(CommunityProblemStatus::class, 'status_id');
    }

    public function claimer()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function linkedProject()
    {
        return $this->belongsTo(Project::class, 'linked_project_id');
    }
}
