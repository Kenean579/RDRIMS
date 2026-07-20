<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Faculty 1: Wollo - Natural and Computational Sciences
        |--------------------------------------------------------------------------
        */

        $fncs = Faculty::where('code', 'WU-DESSIE-FNCS')->first();

        $this->createDepartment(
            'Computer Science',
            'CS',
            $fncs
        );

        $this->createDepartment(
            'Information Technology',
            'IT',
            $fncs
        );

        $this->createDepartment(
            'Physics',
            'PHYS',
            $fncs
        );

        $this->createDepartment(
            'Chemistry',
            'CHEM',
            $fncs
        );

        $this->createDepartment(
            'Mathematics',
            'MATH',
            $fncs
        );

        $this->createDepartment(
            'Biology',
            'BIO',
            $fncs
        );


        /*
        |--------------------------------------------------------------------------
        | Faculty 2: Wollo - Social Sciences
        |--------------------------------------------------------------------------
        */

        $socialScience = Faculty::where('code', 'WU-DESSIE-FSSH')->first();

        $this->createDepartment(
            'Geography & Environmental Studies',
            'GEOG',
            $socialScience
        );

        $this->createDepartment(
            'History & Heritage Management',
            'HIST',
            $socialScience
        );

        $this->createDepartment(
            'Sociology',
            'SOC',
            $socialScience
        );


        /*
        |--------------------------------------------------------------------------
        | Faculty 3: Wollo Engineering
        |--------------------------------------------------------------------------
        */

        $engineering = Faculty::where('code', 'WU-DESSIE-FET')->first();

        $this->createDepartment(
            'Electrical & Computer Engineering',
            'ECE',
            $engineering
        );

        $this->createDepartment(
            'Mechanical Engineering',
            'ME',
            $engineering
        );

        $this->createDepartment(
            'Civil Engineering',
            'CE',
            $engineering
        );


        /*
        |--------------------------------------------------------------------------
        | Faculty 4: Business
        |--------------------------------------------------------------------------
        */

        $business = Faculty::where('code', 'WU-KOM-FBE')->first();

        $this->createDepartment(
            'Accounting and Finance',
            'ACFN',
            $business
        );

        $this->createDepartment(
            'Management',
            'MGMT',
            $business
        );


        /*
        |--------------------------------------------------------------------------
        | Faculty 5: Health Sciences
        |--------------------------------------------------------------------------
        */

        $health = Faculty::where('code', 'WU-KOM-FHS')->first();

        $this->createDepartment(
            'Public Health',
            'PH',
            $health
        );

        $this->createDepartment(
            'Nursing',
            'NURS',
            $health
        );


        /*
        |--------------------------------------------------------------------------
        | Faculty 6: Agriculture
        |--------------------------------------------------------------------------
        */

        $agriculture = Faculty::where('code', 'WU-KOM-FAG')->first();

        $this->createDepartment(
            'Plant Science',
            'PLSC',
            $agriculture
        );

        $this->createDepartment(
            'Animal Science',
            'ANSC',
            $agriculture
        );


        /*
        |--------------------------------------------------------------------------
        | AAU Departments
        |--------------------------------------------------------------------------
        */

        $aauNatural = Faculty::where('code', 'AAU-SK-CNS')->first();

        $this->createDepartment(
            'History',
            'AAU-HIST',
            $aauNatural
        );


        $aauSocial = Faculty::where('code', 'AAU-SK-CSS')->first();

        $this->createDepartment(
            'Political Science',
            'AAU-POLS',
            $aauSocial
        );
    }


    private function createDepartment(
        string $name,
        string $code,
        ?Faculty $faculty
    ): void {
        if (!$faculty) {
            return;
        }

        Department::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'faculty_id' => $faculty->id,
            ]
        );
    }
}
