<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HierarchicalScope
{
    public function scopeHierarchical(Builder $query, User $user, string $userColumn = 'user_id'): Builder
    {
        if ($user->hasRole('super_admin') && !$user->hasRole('research_admin') && !$user->hasRole('campus_admin')
            && !$user->hasRole('faculty_admin') && !$user->hasRole('department_head') && !$user->hasRole('director')
            && !$user->hasRole('researcher') && !$user->hasRole('reviewer') && !$user->hasRole('student')
            && !$user->hasRole('finance_officer') && !$user->hasRole('ethics_officer')) {
            return $query->whereRaw('1 = 0');
        }

        $query->where(function (Builder $q) use ($user, $userColumn) {
            $q->where($this->getTable().'.'.$userColumn, $user->id);

            if ($user->hasRole('reviewer') && $this->getTable() === 'proposals') {
                $q->orWhereIn($this->getTable().'.id', function ($sub) use ($user) {
                    $sub->select('proposal_id')->from('proposal_reviewers')->where('reviewer_id', $user->id);
                });
            }

            if ($user->hasRole('director')) {
                $centerIds = \Illuminate\Support\Facades\DB::table('user_research_centers')
                    ->where('user_id', $user->id)->pluck('research_center_id');

                if ($centerIds->isNotEmpty()) {
                    $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($centerIds) {
                        $sub->select('user_id')->from('user_research_centers')->whereIn('research_center_id', $centerIds);
                    });
                }
            }

            if (!$user->department_id) return;

            if ($user->hasRole('department_head')) {
                $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($user) {
                    $sub->select('id')->from('users')->where('department_id', $user->department_id);
                });
            }

            if ($user->hasRole('faculty_admin')) {
                $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($user) {
                    $sub->select('u.id')->from('users as u')
                        ->join('departments as d', 'u.department_id', '=', 'd.id')
                        ->where('d.faculty_id', function ($facSub) use ($user) {
                            $facSub->select('d2.faculty_id')->from('departments as d2')
                                ->join('users as u2', 'u2.department_id', '=', 'd2.id')
                                ->where('u2.id', $user->id)->take(1);
                        });
                });
            }

            if ($user->hasRole('campus_admin')) {
                $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($user) {
                    $sub->select('u.id')->from('users as u')
                        ->join('departments as d', 'u.department_id', '=', 'd.id')
                        ->join('faculties as f', 'd.faculty_id', '=', 'f.id')
                        ->where('f.campus_id', function ($camSub) use ($user) {
                            $camSub->select('f2.campus_id')->from('faculties as f2')
                                ->join('departments as d2', 'd2.faculty_id', '=', 'f2.id')
                                ->join('users as u2', 'u2.department_id', '=', 'd2.id')
                                ->where('u2.id', $user->id)->take(1);
                        });
                });
            }

            if ($user->hasRole('research_admin', 'finance_officer', 'ethics_officer')) {
                $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($user) {
                    $sub->select('u.id')->from('users as u')
                        ->join('departments as d', 'u.department_id', '=', 'd.id')
                        ->join('faculties as f', 'd.faculty_id', '=', 'f.id')
                        ->join('campuses as c', 'f.campus_id', '=', 'c.id')
                        ->where('c.university_id', $user->university_id);
                });
                
                $q->orWhereIn($this->getTable().'.'.$userColumn, function ($sub) use ($user) {
                    $sub->select('id')->from('users')->where('university_id', $user->university_id);
                });
            }
        });

        $request = request();
        if ($request->hasAny(['university_id', 'campus_id', 'faculty_id', 'department_id'])) {
            $query->whereIn($this->getTable().'.'.$userColumn, function ($sub) use ($request) {
                $sub->select('users.id')->from('users')
                    ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                    ->leftJoin('faculties', 'departments.faculty_id', '=', 'faculties.id')
                    ->leftJoin('campuses', 'faculties.campus_id', '=', 'campuses.id')
                    ->where(function($q) use ($request) {
                        if ($request->department_id) {
                            $q->where('departments.id', $request->department_id);
                        }
                        if ($request->faculty_id) {
                            $q->orWhere('faculties.id', $request->faculty_id);
                        }
                        if ($request->campus_id) {
                            $q->orWhere('campuses.id', $request->campus_id);
                        }
                        if ($request->university_id) {
                            $q->orWhere('campuses.university_id', $request->university_id)
                              ->orWhere('users.university_id', $request->university_id);
                        }
                    });
            });
        }

        return $query;
    }

    public function isManageableBy(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('research_admin')) {
            $userUniversityId = $user->university_id ?: ($user->department?->faculty?->campus?->university_id);
            return $this->university_id === $userUniversityId
                || ($this->createdBy && $this->createdBy->university_id === $userUniversityId);
        }

        if ($user->hasRole('campus_admin') && $user->department_id) {
            $userCampus = $user->department->faculty->campus_id ?? null;
            return $this->campus_id === $userCampus
                || ($this->createdBy && $userCampus
                    && $this->createdBy->department && $this->createdBy->department->faculty
                    && $this->createdBy->department->faculty->campus_id === $userCampus);
        }

        if ($user->hasRole('faculty_admin') && $user->department_id) {
            $userFaculty = $user->department->faculty_id ?? null;
            return $this->faculty_id === $userFaculty
                || ($this->createdBy && $userFaculty && $this->createdBy->department_id
                    && $this->createdBy->department->faculty_id === $userFaculty);
        }

        if ($user->hasRole('department_head') && $user->department_id) {
            return $this->department_id === $user->department_id
                || ($this->createdBy && $this->createdBy->department_id === $user->department_id);
        }

        if ($user->hasRole('director')) {
            $centerIds = $user->research_centers->pluck('id')->toArray();
            return in_array($this->research_center_id, $centerIds)
                || ($this->createdBy && $this->createdBy->research_centers->pluck('id')->intersect($centerIds)->isNotEmpty());
        }

        return false;
    }
}