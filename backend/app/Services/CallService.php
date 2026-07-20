<?php

namespace App\Services;

use App\Models\Call;
use App\Models\User;
use App\Models\CallStatus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Campus;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;

class CallService
{
    /**
     * Create a new call.
     */
    public function create(array $data, User $user): Call
    {
        return DB::transaction(function () use ($data, $user) {

            $this->ensureUserCanCreate($user);

            /*
            |--------------------------------------------------------------------------
            | Resolve hierarchy automatically
            |--------------------------------------------------------------------------
            */

            $data = $this->resolveHierarchy($data, $user);


            /*
            |--------------------------------------------------------------------------
            | Prevent unauthorized hierarchy assignment
            |--------------------------------------------------------------------------
            */

            $this->validateHierarchyOwnership(
                $data,
                $user
            );


            /*
            |--------------------------------------------------------------------------
            | Default values
            |--------------------------------------------------------------------------
            */

            $data['created_by'] = $user->id;


            if (empty($data['status_id'])) {

                $data['status_id'] = CallStatus::where(
                    'name',
                    'open'
                )->value('id');
            }


            /*
            |--------------------------------------------------------------------------
            | Create call
            |--------------------------------------------------------------------------
            */

            return Call::create($data);

        });
    }


    /**
     * Update existing call.
     */
    public function update(
        Call $call,
        array $data,
        User $user
    ): Call {

        return DB::transaction(function () use (
            $call,
            $data,
            $user
        ) {


            $this->ensureUserCanManage(
                $call,
                $user
            );


            /*
            |--------------------------------------------------------------------------
            | Resolve hierarchy if changed
            |--------------------------------------------------------------------------
            */

            $data = $this->resolveHierarchy(
                $data,
                $user
            );


            $this->validateHierarchyOwnership(
                $data,
                $user
            );


            $call->update($data);


            return $call->refresh();

        });
    }



    /**
     * Delete call.
     */
    public function delete(
        Call $call,
        User $user
    ): bool {

        $this->ensureUserCanManage(
            $call,
            $user
        );


        return $call->delete();
    }



    /**
     * Restore deleted call.
     */
    public function restore(
        Call $call,
        User $user
    ): bool {


        $this->ensureUserCanManage(
            $call,
            $user
        );


        return $call->restore();
    }



    /**
     * Resolve hierarchy relationships.
     *
     * Department
     *      |
     *      Faculty
     *          |
     *          Campus
     *              |
     *              University
     */
    private function resolveHierarchy(
        array $data,
        User $user
    ): array {


        /*
        |--------------------------------------------------------------------------
        | Department selected
        |--------------------------------------------------------------------------
        */

        if (!empty($data['department_id'])) {


            $department = Department::with(
                'faculty.campus'
            )
            ->findOrFail(
                $data['department_id']
            );


            $data['department_id']
                = $department->id;


            $data['faculty_id']
                = $department->faculty_id;


            $data['campus_id'] = $department->faculty ? $department->faculty->campus_id : null;

            $data['university_id'] = ($department->faculty && $department->faculty->campus) ? $department->faculty->campus->university_id : null;

        }



        /*
        |--------------------------------------------------------------------------
        | Faculty selected
        |--------------------------------------------------------------------------
        */

        elseif (!empty($data['faculty_id'])) {


            $faculty = Faculty::with(
                'campus'
            )
            ->findOrFail(
                $data['faculty_id']
            );


            $data['faculty_id']
                = $faculty->id;


            $data['campus_id']
                = $faculty->campus_id;


            $data['university_id'] = $faculty->campus ? $faculty->campus->university_id : null;

        }



        /*
        |--------------------------------------------------------------------------
        | Campus selected
        |--------------------------------------------------------------------------
        */

        elseif (!empty($data['campus_id'])) {


            $campus = Campus::findOrFail(
                $data['campus_id']
            );


            $data['campus_id']
                = $campus->id;


            $data['university_id']
                = $campus->university_id;

        }



        /*
        |--------------------------------------------------------------------------
        | No hierarchy provided
        |--------------------------------------------------------------------------
        */

        elseif (
            empty($data['university_id'])
            &&
            !$user->hasRole('super_admin')
        ) {


            $data['university_id']
                = $user->resolvedUniversityId();

        }


        return $data;
    }




    /**
     * Validate user scope before creating/updating.
     */
    private function validateHierarchyOwnership(
        array $data,
        User $user
    ): void {


        if ($user->hasRole('super_admin')) {
            return;
        }



        $universityId =
            $user->resolvedUniversityId();



        /*
        |--------------------------------------------------------------------------
        | University Isolation
        |--------------------------------------------------------------------------
        */

        if (
            isset($data['university_id'])
            &&
            $data['university_id'] != $universityId
        ) {

            throw new AuthorizationException(
                'You cannot create or modify calls outside your university.'
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Campus Isolation
        |--------------------------------------------------------------------------
        */

        if (
            $user->hasRole('campus_admin')
            &&
            isset($data['campus_id'])
            &&
            $data['campus_id'] != $user->campus_id
        ) {

            throw new AuthorizationException(
                'You cannot manage calls outside your campus.'
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Faculty Isolation
        |--------------------------------------------------------------------------
        */

        if (
            $user->hasRole('faculty_admin')
            &&
            isset($data['faculty_id'])
            &&
            $data['faculty_id'] != $user->faculty_id
        ) {

            throw new AuthorizationException(
                'You cannot manage calls outside your faculty.'
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Department Isolation
        |--------------------------------------------------------------------------
        */

        if (
            $user->hasRole('department_head')
            &&
            isset($data['department_id'])
            &&
            $data['department_id'] != $user->department_id
        ) {

            throw new AuthorizationException(
                'You cannot manage calls outside your department.'
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Research Center Isolation
        |--------------------------------------------------------------------------
        */

        if (
            $user->hasRole('director')
            &&
            isset($data['research_center_id'])
        ) {


            $allowed =
                $user->researchCenters()
                ->whereKey(
                    $data['research_center_id']
                )
                ->exists();



            if (!$allowed) {

                throw new AuthorizationException(
                    'You cannot manage this research center call.'
                );
            }
        }

    }




    /**
     * Check create permission.
     */
    private function ensureUserCanCreate(
        User $user
    ): void {


        if (!$user->isAdmin()) {

            throw new AuthorizationException(
                'You do not have permission to create calls.'
            );

        }

    }




    /**
     * Check update/delete permission.
     */
    private function ensureUserCanManage(
        Call $call,
        User $user
    ): void {


        if ($user->hasRole('super_admin')) {
            return;
        }



        if (
            !$user->isAdmin()
        ) {

            throw new AuthorizationException(
                'You do not have permission to manage calls.'
            );

        }



        if (
            (int)$call->university_id
            !==
            (int)$user->resolvedUniversityId()
        ) {


            throw new AuthorizationException(
                'You cannot access calls from another university.'
            );

        }

    }

}