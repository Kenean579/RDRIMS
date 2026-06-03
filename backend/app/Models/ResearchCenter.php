<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Traits\HierarchicalScope;

class ResearchCenter extends Model
{
    use HasFactory, HierarchicalScope;

    protected $fillable = [
        'name', 'code', 'director_id', 'logo_file_id',
        'university_id', 'campus_id', 'faculty_id', 'department_id',
        'description', 'established_at'
    ];

    protected $casts = [
        'established_at' => 'date',
    ];

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_research_centers')
                    ->withPivot('center_role_id')
                    ->withTimestamps()
                    ->using(UserResearchCenter::class);
    }
}
