<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AauFinanceOfficerSeeder extends Seeder
{
    public function run(): void
    {
        $university = University::where('code', 'AAU')->first();
        $department = Department::where('code', 'AAU-HIST')->first();
        $financeOfficerRole = Role::where('name', 'finance_officer')->first();

        if (!$university || !$department || !$financeOfficerRole) {
            throw new RuntimeException('AAU, AAU-HIST department, and finance_officer role must be seeded first.');
        }

        $officer = User::updateOrCreate(
            ['email' => 'finance.officer@aau.edu.et'],
            [
                'name' => 'Ato Dawit Alemu',
                'password' => Hash::make('Password@123'),
                'university_id' => $university->id,
                'department_id' => $department->id,
                'is_active' => true,
                'bio' => 'Finance Officer responsible for research budget reviews at Addis Ababa University.',
            ]
        );

        $assignedBy = User::whereHas('roles', fn ($query) => $query->where('name', 'super_admin'))
            ->value('id');

        $officer->roles()->syncWithoutDetaching([
            $financeOfficerRole->id => [
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
            ],
        ]);
    }
}
