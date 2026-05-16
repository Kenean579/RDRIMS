<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        // Dessie Campus (id=1)
        Faculty::create(['name' => 'Faculty of Natural and Computational Sciences', 'code' => 'WU-DESSIE-FNCS', 'campus_id' => 1]);
        Faculty::create(['name' => 'Faculty of Social Sciences and Humanities', 'code' => 'WU-DESSIE-FSSH', 'campus_id' => 1]);
        Faculty::create(['name' => 'Faculty of Engineering and Technology', 'code' => 'WU-DESSIE-FET', 'campus_id' => 1]);

        // Kombolcha Campus (id=2)
        Faculty::create(['name' => 'Faculty of Business and Economics', 'code' => 'WU-KOM-FBE', 'campus_id' => 2]);
        Faculty::create(['name' => 'Faculty of Health Sciences', 'code' => 'WU-KOM-FHS', 'campus_id' => 2]);
        Faculty::create(['name' => 'Faculty of Agriculture', 'code' => 'WU-KOM-FAG', 'campus_id' => 2]);

        // AAU - Siddist Kilo (id=3)
        Faculty::create(['name' => 'College of Natural Sciences', 'code' => 'AAU-SK-CNS', 'campus_id' => 3]);
        Faculty::create(['name' => 'College of Social Sciences', 'code' => 'AAU-SK-CSS', 'campus_id' => 3]);

        // AAU - Arat Kilo (id=4)
        Faculty::create(['name' => 'College of Law and Governance', 'code' => 'AAU-AK-CLG', 'campus_id' => 4]);
        Faculty::create(['name' => 'College of Education', 'code' => 'AAU-AK-CE', 'campus_id' => 4]);

        // BDU - Main (id=6)
        Faculty::create(['name' => 'Faculty of Electrical and Computer Engineering', 'code' => 'BDU-MAIN-FECE', 'campus_id' => 6]);
        Faculty::create(['name' => 'Faculty of Civil and Water Resources Engineering', 'code' => 'BDU-MAIN-FCWRE', 'campus_id' => 6]);
    }
}