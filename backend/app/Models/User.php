<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name', 'email', 'password', 'department_id', 'profile_image_id',
        'orcid_id', 'google_scholar_id', 'scopus_id', 'linkedin_url',
        'is_active', 'bio'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function profileImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'profile_image_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('assigned_by', 'assigned_at')
                    ->withTimestamps()
                    ->using(UserRole::class);
    }

    public function researchCenters(): BelongsToMany
    {
        return $this->belongsToMany(ResearchCenter::class, 'user_research_centers')
                    ->withPivot('center_role_id')
                    ->withTimestamps()
                    ->using(UserResearchCenter::class);
    }

    public function expertise(): BelongsToMany
    {
        return $this->belongsToMany(Expertise::class, 'user_expertise')
                    ->using(UserExpertise::class);
    }

    public function languagePreference(): HasOne
    {
        return $this->hasOne(LanguagePreference::class);
    }

    public function submittedProposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'submitted_by');
    }

    public function approvedProposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'approved_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
