<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        Faculty::firstOrCreate(['name' => 'Faculty of Natural and Computational Sciences', 'code' => 'WU-DESSIE-FNCS', 'campus_id' => 1]);
        Faculty::firstOrCreate(['name' => 'Faculty of Social Sciences and Humanities', 'code' => 'WU-DESSIE-FSSH', 'campus_id' => 1]);
        Faculty::firstOrCreate(['name' => 'Faculty of Engineering and Technology', 'code' => 'WU-DESSIE-FET', 'campus_id' => 1]);
        Faculty::firstOrCreate(['name' => 'Faculty of Business and Economics', 'code' => 'WU-KOM-FBE', 'campus_id' => 2]);
        Faculty::firstOrCreate(['name' => 'Faculty of Health Sciences', 'code' => 'WU-KOM-FHS', 'campus_id' => 2]);
        Faculty::firstOrCreate(['name' => 'Faculty of Agriculture', 'code' => 'WU-KOM-FAG', 'campus_id' => 2]);
        Faculty::firstOrCreate(['name' => 'College of Natural Sciences', 'code' => 'AAU-SK-CNS', 'campus_id' => 3]);
        Faculty::firstOrCreate(['name' => 'College of Social Sciences', 'code' => 'AAU-SK-CSS', 'campus_id' => 3]);
        Faculty::firstOrCreate(['name' => 'College of Law and Governance', 'code' => 'AAU-AK-CLG', 'campus_id' => 4]);
        Faculty::firstOrCreate(['name' => 'College of Education', 'code' => 'AAU-AK-CE', 'campus_id' => 4]);
    }
}
