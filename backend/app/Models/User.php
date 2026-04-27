<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'profile_image_id',
        'orcid_id',
        'google_scholar_id',
        'scopus_id',
        'linkedin_url',
        'is_active',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function profileImage()
    {
        return $this->belongsTo(File::class, 'profile_image_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
                    ->withPivot(['assigned_by', 'assigned_at']);
    }

    public function researchCenters()
    {
        return $this->belongsToMany(ResearchCenter::class, 'user_research_centers', 'user_id', 'research_center_id')
                    ->withPivot('center_role_id');
    }

    public function expertise()
    {
        return $this->belongsToMany(Expertise::class, 'user_expertise', 'user_id', 'expertise_id');
    }
}
