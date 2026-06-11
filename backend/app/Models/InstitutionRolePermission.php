<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionRolePermission extends Model
{
    protected $table = 'institution_role_permissions';

    protected $fillable = [
        'university_id',
        'campus_id',
        'faculty_id',
        'department_id',
        'research_center_id',
        'role_id',
        'permission_id',
        'granted'
    ];

    protected $casts = [
        'granted' => 'boolean'
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
