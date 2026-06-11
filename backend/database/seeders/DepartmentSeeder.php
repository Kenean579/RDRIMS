<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::firstOrCreate(['name' => 'Computer Science', 'code' => 'CS', 'faculty_id' => 1]);
        Department::firstOrCreate(['name' => 'Information Technology', 'code' => 'IT', 'faculty_id' => 1]);
        Department::firstOrCreate(['name' => 'Physics', 'code' => 'PHYS', 'faculty_id' => 1]);
        Department::firstOrCreate(['name' => 'Chemistry', 'code' => 'CHEM', 'faculty_id' => 1]);
        Department::firstOrCreate(['name' => 'Mathematics', 'code' => 'MATH', 'faculty_id' => 1]);
        Department::firstOrCreate(['name' => 'Biology', 'code' => 'BIO', 'faculty_id' => 1]);
        Department::firstOrCreate(['name' => 'Geography & Environmental Studies', 'code' => 'GEOG', 'faculty_id' => 2]);
        Department::firstOrCreate(['name' => 'History & Heritage Management', 'code' => 'HIST', 'faculty_id' => 2]);
        Department::firstOrCreate(['name' => 'Sociology', 'code' => 'SOC', 'faculty_id' => 2]);
        Department::firstOrCreate(['name' => 'Electrical & Computer Engineering', 'code' => 'ECE', 'faculty_id' => 3]);
        Department::firstOrCreate(['name' => 'Mechanical Engineering', 'code' => 'ME', 'faculty_id' => 3]);
        Department::firstOrCreate(['name' => 'Civil Engineering', 'code' => 'CE', 'faculty_id' => 3]);
        Department::firstOrCreate(['name' => 'Accounting and Finance', 'code' => 'ACFN', 'faculty_id' => 4]);
        Department::firstOrCreate(['name' => 'Management', 'code' => 'MGMT', 'faculty_id' => 4]);
        Department::firstOrCreate(['name' => 'Public Health', 'code' => 'PH', 'faculty_id' => 5]);
        Department::firstOrCreate(['name' => 'Nursing', 'code' => 'NURS', 'faculty_id' => 5]);
        Department::firstOrCreate(['name' => 'Plant Science', 'code' => 'PLSC', 'faculty_id' => 6]);
        Department::firstOrCreate(['name' => 'Animal Science', 'code' => 'ANSC', 'faculty_id' => 6]);
        Department::firstOrCreate(['name' => 'History', 'code' => 'AAU-HIST', 'faculty_id' => 7]);
        Department::firstOrCreate(['name' => 'Political Science', 'code' => 'AAU-POLS', 'faculty_id' => 8]);
    }
}
