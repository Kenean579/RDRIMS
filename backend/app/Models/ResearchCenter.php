<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ResearchCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'director_id', 'logo_file_id',
        'parent_university_id', 'parent_campus_id', 'parent_faculty_id',
        'description'
    ];

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function parentUniversity(): BelongsTo
    {
        return $this->belongsTo(University::class, 'parent_university_id');
    }

    public function parentCampus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'parent_campus_id');
    }

    public function parentFaculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'parent_faculty_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_research_centers')
                    ->withPivot('center_role_id')
                    ->withTimestamps()
                    ->using(UserResearchCenter::class);
    }
}
