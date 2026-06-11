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
            ['name' => 'Dr. Tigist Haile', 'email' => 'tigist.researcher@wollo.edu.et', 'role' => 'researcher', 'department_id' => 1, 'orcid_id' => '0000-0001-1111-2222', 'bio' => 'Associate Professor of Computer Science. Research in AI and NLP.'],
            ['name' => 'Dr. Henok Tesfaye', 'email' => 'henok.researcher@wollo.edu.et', 'role' => 'researcher', 'department_id' => 3, 'orcid_id' => '0000-0001-3333-4444', 'bio' => 'Assistant Professor of Physics. Research in renewable energy.'],
            ['name' => 'Dr. Sara Mohammed', 'email' => 'sara.researcher@wollo.edu.et', 'role' => 'researcher', 'department_id' => 15, 'orcid_id' => '0000-0001-5555-6666', 'bio' => 'Lecturer in Public Health. Research in maternal and child health.'],
            ['name' => 'Prof. Yonas Mulugeta', 'email' => 'yonas.reviewer@wollo.edu.et', 'role' => 'reviewer', 'department_id' => 1, 'bio' => 'Professor of Computer Science. Expert in AI and data science.'],
            ['name' => 'Dr. Frehiwot Assefa', 'email' => 'frehiwot.reviewer@wollo.edu.et', 'role' => 'reviewer', 'department_id' => 7, 'bio' => 'Associate Professor of Geography. Expert in climate change research.'],
            ['name' => 'Dr. Daniel Bekele', 'email' => 'daniel.reviewer@wollo.edu.et', 'role' => 'reviewer', 'department_id' => 11, 'bio' => 'Associate Professor of Mechanical Engineering.'],
            ['name' => 'Ato Solomon Tesfaye', 'email' => 'solomon.finance@wollo.edu.et', 'role' => 'finance_officer', 'department_id' => 13, 'bio' => 'Senior Finance Officer, Research Directorate.'],
            ['name' => 'Dr. Genet Worku', 'email' => 'genet.ethics@wollo.edu.et', 'role' => 'ethics_officer', 'department_id' => 15, 'bio' => 'Ethics Committee Chair, Wollo University IRB.'],
            ['name' => 'Dr. Worku Gemechu', 'email' => 'worku.depthead@wollo.edu.et', 'role' => 'department_head', 'department_id' => 1, 'bio' => 'Head, Department of Computer Science.'],
            ['name' => 'Prof. Meseret Asnake', 'email' => 'meseret.director@wollo.edu.et', 'role' => 'director', 'department_id' => 1, 'bio' => 'Director, ICT and Digital Innovation Research Center.'],
            ['name' => 'Blen Alemu', 'email' => 'blen.student@wollo.edu.et', 'role' => 'student', 'department_id' => 1, 'bio' => 'MSc student in Computer Science.'],
            ['name' => 'Dawit Tadesse', 'email' => 'dawit.student@wollo.edu.et', 'role' => 'student', 'department_id' => 15, 'bio' => 'PhD candidate in Public Health.'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('Password@123'),
                    'department_id' => $u['department_id'],
                    'university_id' => 1,
                    'is_active' => true,
                    'orcid_id' => $u['orcid_id'] ?? null,
                    'bio' => $u['bio'] ?? null,
                ]
            );
            $role = Role::where('name', $u['role'])->first();
            if ($role && !$user->roles->contains($role->id)) {
                $user->roles()->attach($role->id, ['assigned_by' => 1, 'assigned_at' => now()]);
            }
        }
    }
}
