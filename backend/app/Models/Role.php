<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'university_id', 'campus_id', 'faculty_id', 'department_id', 'research_center_id'
    ];

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

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class);
    }

    public function institutionOverrides(): HasMany
    {
        return $this->hasMany(InstitutionRolePermission::class, 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
                    ->using(RolePermission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withPivot('assigned_by', 'assigned_at')
                    ->withTimestamps()
                    ->using(UserRole::class);
    }
}