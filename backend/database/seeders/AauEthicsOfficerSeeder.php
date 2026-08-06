<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AauEthicsOfficerSeeder extends Seeder
{
    public function run(): void
    {
        $university = University::where('code', 'AAU')->first();
        $department = Department::where('code', 'AAU-HIST')->first();
        $ethicsOfficerRole = Role::where('name', 'ethics_officer')->first();

        if (!$university || !$department || !$ethicsOfficerRole) {
            throw new RuntimeException('AAU, AAU-HIST department, and ethics_officer role must be seeded first.');
        }

        $officer = User::updateOrCreate(
            ['email' => 'ethics.officer@aau.edu.et'],
            [
                'name' => 'Dr. Hana Bekele',
                'password' => Hash::make('Password@123'),
                'university_id' => $university->id,
                'department_id' => $department->id,
                'is_active' => true,
                'bio' => 'Ethics Officer for the Addis Ababa University Institutional Review Board.',
            ]
        );

        $assignedBy = User::whereHas('roles', fn ($query) => $query->where('name', 'super_admin'))
            ->value('id');

        $officer->roles()->syncWithoutDetaching([
            $ethicsOfficerRole->id => [
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
            ],
        ]);
    }
}
