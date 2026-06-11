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
use App\Models\Proposal;
use App\Models\ProposalReviewer;
use App\Models\Publication;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory, \App\Traits\HasRoles, \App\Traits\HierarchicalScope;

/**
     * @property int $id
     * @property int|null $university_id
     * @property int|null $department_id
     * @property string $name
     * @property string $email
     * @property string $password
     * @property int|null $profile_image_id
     * @property int|null $research_center_id
     * @property int|null $center_role_id
     * @property string|null $orcid_id
     * @property string|null $google_scholar_id
     * @property string|null $scopus_id
     * @property string|null $linkedin_url
     * @property bool $is_active
     * @property string|null $bio
     * @property string|null $expertise_keywords
     */
    protected $fillable = [
        'name', 'email', 'password', 'university_id', 'department_id', 'profile_image_id',
        'research_center_id', 'center_role_id',
        'orcid_id', 'google_scholar_id', 'scopus_id', 'linkedin_url',
        'is_active', 'bio', 'expertise_keywords'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class, 'research_center_id');
    }

    public function centerRole(): BelongsTo
    {
        return $this->belongsTo(CenterRole::class, 'center_role_id');
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
        return $this->belongsToMany(Expertise::class, 'user_expertises')
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
    
    // Proposals reviewed by this user (via proposal_reviewers pivot)
    public function reviewedProposals(): BelongsToMany
    {
        return $this->belongsToMany(Proposal::class, 'proposal_reviewers', 'reviewer_id', 'proposal_id')
            ->as('reviewPivot')
            ->withPivot(
                'id', 'assigned_by', 'assigned_at', 'submitted_at',
                'overall_score', 'overall_comments', 'decision_id'
            )
            ->withTimestamps()
            ->using(ProposalReviewer::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'publication_authors')
                    ->withPivot('external_author_name', 'external_institution', 'author_order')
                    ->withTimestamps();
    }

    /**
     * Get all effective permission IDs for the user based on hierarchical multi-tenancy.
     */
    public function getEffectivePermissionIds(): array
    {
        $cacheKey = "user_{$this->id}_effective_permissions_v2";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $universityId = $this->university_id ?: $this->department?->faculty?->campus?->university_id;

            // 1. Global roles (university_id IS NULL)
            $globalRoleIds = $this->roles()->whereNull('university_id')->pluck('roles.id');
            $globalPermIds = \App\Models\Permission::whereHas('roles', function ($q) use ($globalRoleIds) {
                $q->whereIn('role_id', $globalRoleIds);
            })->pluck('id');

            // 2. Institution-specific roles (university_id = user's university)
            $instRoleIds = $universityId 
                ? $this->roles()->where('university_id', $universityId)->pluck('roles.id')
                : collect([]);
                
            $instPermIds = $instRoleIds->isNotEmpty() 
                ? \App\Models\Permission::whereHas('roles', function ($q) use ($instRoleIds) {
                    $q->whereIn('role_id', $instRoleIds);
                })->pluck('id')
                : collect([]);

            // 3. Overrides for this institution
            $userRoleIds = $this->roles->pluck('id');
            $overrides = $universityId 
                ? \App\Models\InstitutionRolePermission::where('university_id', $universityId)
                    ->whereIn('role_id', $userRoleIds)
                    ->get()
                : collect([]);

            $added = $overrides->where('granted', true)->pluck('permission_id');
            $removed = $overrides->where('granted', false)->pluck('permission_id');

            // Merge: (Global + Institutional + Added) - Removed
            $all = $globalPermIds->merge($instPermIds)->merge($added)->diff($removed)->unique();
            
            return $all->values()->toArray();
        });
    }
}
