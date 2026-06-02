<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Researchers
            [
                'name' => 'Dr. Tigist Haile',
                'email' => 'tigist.researcher@rdrims.local',
                'role' => 'researcher',
                'department_id' => 1,
                'orcid_id' => '0000-0001-1111-2222',
                'bio' => 'Associate Professor of Computer Science. Research interests in AI and NLP.',
            ],
            [
                'name' => 'Dr. Henok Tesfaye',
                'email' => 'henok.researcher@rdrims.local',
                'role' => 'researcher',
                'department_id' => 3,
                'orcid_id' => '0000-0001-3333-4444',
                'bio' => 'Assistant Professor of Physics. Research in renewable energy.',
            ],
            [
                'name' => 'Dr. Sara Mohammed',
                'email' => 'sara.researcher@rdrims.local',
                'role' => 'researcher',
                'department_id' => 17,
                'orcid_id' => '0000-0001-5555-6666',
                'bio' => 'Lecturer in Public Health. Research in maternal and child health.',
            ],

            // Reviewers
            [
                'name' => 'Prof. Yonas Mulugeta',
                'email' => 'yonas.reviewer@rdrims.local',
                'role' => 'reviewer',
                'department_id' => 1,
                'bio' => 'Professor of Computer Science. Expert in AI and data science.',
            ],
            [
                'name' => 'Dr. Frehiwot Assefa',
                'email' => 'frehiwot.reviewer@rdrims.local',
                'role' => 'reviewer',
                'department_id' => 7,
                'bio' => 'Associate Professor of Geography. Expert in climate change research.',
            ],
            [
                'name' => 'Dr. Daniel Bekele',
                'email' => 'daniel.reviewer@rdrims.local',
                'role' => 'reviewer',
                'department_id' => 11,
                'bio' => 'Associate Professor of Mechanical Engineering.',
            ],

            // Finance Officer
            [
                'name' => 'Ato Solomon Tesfaye',
                'email' => 'solomon.finance@rdrims.local',
                'role' => 'finance_officer',
                'department_id' => 14,
                'bio' => 'Senior Finance Officer, Research Directorate.',
            ],

            // Ethics Officer
            [
                'name' => 'Dr. Genet Worku',
                'email' => 'genet.ethics@rdrims.local',
                'role' => 'ethics_officer',
                'department_id' => 17,
                'bio' => 'Ethics Committee Chair, Institutional IRB.',
            ],

            // Department Head
            [
                'name' => 'Dr. Worku Gemechu',
                'email' => 'worku.depthead@rdrims.local',
                'role' => 'department_head',
                'department_id' => 1,
                'bio' => 'Head, Department of Computer Science.',
            ],

            // Director
            [
                'name' => 'Prof. Meseret Asnake',
                'email' => 'meseret.director@rdrims.local',
                'role' => 'director',
                'department_id' => 1,
                'bio' => 'Director, ICT and Digital Innovation Research Center.',
            ],

            // Students
            [
                'name' => 'Blen Alemu',
                'email' => 'blen.student@rdrims.local',
                'role' => 'student',
                'department_id' => 1,
                'bio' => 'MSc student in Computer Science.',
            ],
            [
                'name' => 'Dawit Tadesse',
                'email' => 'dawit.student@rdrims.local',
                'role' => 'student',
                'department_id' => 17,
                'bio' => 'PhD candidate in Public Health.',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('Password@123'),
                'department_id' => $userData['department_id'],
                'is_active' => true,
                'orcid_id' => $userData['orcid_id'] ?? null,
                'bio' => $userData['bio'] ?? null,
            ]);

            $role = Role::where('name', $userData['role'])->first();
            $user->roles()->attach($role->id, [
                'assigned_by' => 1, // super admin
                'assigned_at' => now(),
            ]);
        }
    }
}