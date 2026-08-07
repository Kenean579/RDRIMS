<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FinanceOfficerSeeder extends Seeder
{
    public function run(): void
    {
        // Addis Ababa University
        $universityId = 2;

        // Use an existing AAU department from your DepartmentSeeder
        $department = Department::where('code', 'AAU-POLS')->first();

        if (!$department) {
            $this->command->error('AAU department (AAU-POLS) not found. Run DepartmentSeeder first.');
            return;
        }

        $financeRole = Role::where('name', 'finance_officer')->first();

        if (!$financeRole) {
            $this->command->error('finance_officer role not found. Run RoleSeeder first.');
            return;
        }

        $users = [
            [
                'name'  => 'Dr. Selamawit Alemu',
                'email' => 'selamawit.finance@aau.edu.et',
                'bio'   => 'Senior Finance Officer, Addis Ababa University Research Directorate.',
            ],
            [
                'name'  => 'Ato Bekele Tadesse',
                'email' => 'bekele.finance@aau.edu.et',
                'bio'   => 'Finance Officer, Addis Ababa University Research Directorate.',
            ],
            [
                'name'  => 'W/ro Hana Girma',
                'email' => 'hana.finance@aau.edu.et',
                'bio'   => 'Budget and Grant Finance Officer, Addis Ababa University.',
            ],
        ];

        foreach ($users as $u) {

            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'          => $u['name'],
                    'password'      => Hash::make('Password@123'),
                    'department_id' => $department->id,
                    'university_id' => $universityId,
                    'is_active'     => true,
                    'bio'           => $u['bio'],
                ]
            );

            $user->roles()->syncWithoutDetaching([
                $financeRole->id => [
                    'assigned_by' => 1,
                    'assigned_at' => now(),
                ]
            ]);
        }
    }
}
