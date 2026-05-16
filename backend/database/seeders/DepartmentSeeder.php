<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // FNCS (id=1)
        Department::create(['name' => 'Computer Science', 'code' => 'CS', 'faculty_id' => 1]);
        Department::create(['name' => 'Information Technology', 'code' => 'IT', 'faculty_id' => 1]);
        Department::create(['name' => 'Physics', 'code' => 'PHYS', 'faculty_id' => 1]);
        Department::create(['name' => 'Chemistry', 'code' => 'CHEM', 'faculty_id' => 1]);
        Department::create(['name' => 'Mathematics', 'code' => 'MATH', 'faculty_id' => 1]);
        Department::create(['name' => 'Biology', 'code' => 'BIO', 'faculty_id' => 1]);

        // FSSH (id=2)
        Department::create(['name' => 'Geography and Environmental Studies', 'code' => 'GEOG', 'faculty_id' => 2]);
        Department::create(['name' => 'History and Heritage Management', 'code' => 'HIST', 'faculty_id' => 2]);
        Department::create(['name' => 'Sociology', 'code' => 'SOC', 'faculty_id' => 2]);
        Department::create(['name' => 'Psychology', 'code' => 'PSY', 'faculty_id' => 2]);

        // FET (id=3)
        Department::create(['name' => 'Electrical and Computer Engineering', 'code' => 'ECE', 'faculty_id' => 3]);
        Department::create(['name' => 'Mechanical Engineering', 'code' => 'ME', 'faculty_id' => 3]);
        Department::create(['name' => 'Civil Engineering', 'code' => 'CE', 'faculty_id' => 3]);

        // FBE (id=4)
        Department::create(['name' => 'Accounting and Finance', 'code' => 'ACFN', 'faculty_id' => 4]);
        Department::create(['name' => 'Management', 'code' => 'MGMT', 'faculty_id' => 4]);
        Department::create(['name' => 'Economics', 'code' => 'ECON', 'faculty_id' => 4]);

        // FHS (id=5)
        Department::create(['name' => 'Public Health', 'code' => 'PH', 'faculty_id' => 5]);
        Department::create(['name' => 'Nursing', 'code' => 'NURS', 'faculty_id' => 5]);
        Department::create(['name' => 'Pharmacy', 'code' => 'PHARM', 'faculty_id' => 5]);

        // FAG (id=6)
        Department::create(['name' => 'Plant Science', 'code' => 'PLSC', 'faculty_id' => 6]);
        Department::create(['name' => 'Animal Science', 'code' => 'ANSC', 'faculty_id' => 6]);
    }
}