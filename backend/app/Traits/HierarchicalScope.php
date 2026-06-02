<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HierarchicalScope
{
    /**
     * Scope a query based on the user's hierarchical role within the institution.
     * 
     * @param Builder $query
     * @param User $user
     * @param string $userColumn The database column that references the User ID (e.g., 'pi_id', 'submitted_by')
     * @return Builder
     */
    public function scopeHierarchical(Builder $query, User $user, string $userColumn = 'user_id'): Builder
    {
        // 1. Role-based Visibility Constraints
        // Only apply constraints to non-admin/officer roles
        if (!$user->hasRole('super_admin', 'research_admin', 'admin', 'finance_officer', 'ethics_officer')) {
            $query->where(function (Builder $q) use ($user, $userColumn) {
                // Base rule: Everyone clearly sees their own records
                $q->where($this->getTable().'.'.$userColumn, $user->id);

                // Reviewers can see proposals they are assigned to review.
                if ($user->hasRole('reviewer') && $this->getTable() === 'proposals') {
                    $q->orWhereIn($this->getTable().'.id', function ($sub) use ($user) {
                        $sub->select('proposal_id')->from('proposal_reviewers')
                            ->where('reviewer_id', $user->id);
                    });
                }

                // Level: Department Head
                if ($user->hasRole('department_head') && $user->department_id) {
                    $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($user) {
                        $sub->select('id')->from('users')->where('department_id', $user->department_id);
                    });
                }

                // Level: Faculty Dean
                if ($user->hasRole('faculty_dean') && $user->department_id) {
                    $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($user) {
                        $sub->select('users.id')->from('users')
                            ->join('departments', 'users.department_id', '=', 'departments.id')
                            ->where('departments.faculty_id', function ($fSub) use ($user) {
                                $fSub->select('faculty_id')->from('departments')->where('id', $user->department_id)->limit(1);
                            });
                    });
                }

                // Level: Center Director
                if ($user->hasRole('director')) {
                    $centerIds = $user->researchCenters()->pluck('research_centers.id');
                    if ($centerIds->isNotEmpty()) {
                        $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($centerIds) {
                            $sub->select('user_id')->from('user_research_centers')
                                ->whereIn('research_center_id', $centerIds);
                        });
                    }
                }
            });
        }

        // 2. User-Selected Context Filters (Applies to EVERYONE, including Super Admins)
        $request = request();
        if ($request->hasAny(['university_id', 'campus_id', 'faculty_id', 'department_id'])) {
            $query->whereIn($this->getTable().'.'.$userColumn, function ($sub) use ($request) {
                $sub->select('users.id')->from('users')
                    ->join('departments', 'users.department_id', '=', 'departments.id')
                    ->join('faculties', 'departments.faculty_id', '=', 'faculties.id')
                    ->join('campuses', 'faculties.campus_id', '=', 'campuses.id')
                    ->when($request->department_id, fn($q) => $q->where('departments.id', $request->department_id))
                    ->when($request->faculty_id, fn($q) => $q->where('faculties.id', $request->faculty_id))
                    ->when($request->campus_id, fn($q) => $q->where('campuses.id', $request->campus_id))
                    ->when($request->university_id, fn($q) => $q->where('campuses.university_id', $request->university_id));
            });
        }

        return $query;
    }
}
