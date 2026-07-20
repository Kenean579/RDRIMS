<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        University::updateOrCreate(
            ['code' => 'WU'],
            [
                'name' => 'Wollo University',
                'location' => 'Dessie, Ethiopia',
            ]
        );

        University::updateOrCreate(
            ['code' => 'AAU'],
            [
                'name' => 'Addis Ababa University',
                'location' => 'Addis Ababa, Ethiopia',
            ]
        );

        University::updateOrCreate(
            ['code' => 'BDU'],
            [
                'name' => 'Bahir Dar University',
                'location' => 'Bahir Dar, Ethiopia',
            ]
        );

        University::updateOrCreate(
            ['code' => 'GoU'],
            [
                'name' => 'Gonder University',
                'location' => 'Gonder, Ethiopia',
            ]
        );
    }
}
