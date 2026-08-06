<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Expertise;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AauReviewerSeeder extends Seeder
{
    public function run(): void
    {
        $university = University::where('code', 'AAU')->first();
        $department = Department::where('code', 'AAU-HIST')->first();
        $reviewerRole = Role::where('name', 'reviewer')->first();

        if (!$university || !$department || !$reviewerRole) {
            throw new RuntimeException('AAU, AAU-HIST department, and reviewer role must be seeded first.');
        }

        $reviewer = User::updateOrCreate(
            ['email' => 'selam.reviewer@aau.edu.et'],
            [
                'name' => 'Dr. Selam Tesfaye',
                'password' => Hash::make('Password@123'),
                'university_id' => $university->id,
                'department_id' => $department->id,
                'is_active' => true,
                'bio' => 'Addis Ababa University reviewer with expertise in social science and development research.',
                'expertise_keywords' => 'development economics,gender studies,sociology,education',
            ]
        );

        $assignedBy = User::whereHas('roles', fn ($query) => $query->where('name', 'super_admin'))
            ->value('id');

        $reviewer->roles()->syncWithoutDetaching([
            $reviewerRole->id => [
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
            ],
        ]);

        $reviewer->expertise()->syncWithoutDetaching(
            Expertise::whereIn('name', [
                'Development Economics',
                'Education',
                'Gender Studies',
                'Sociology',
            ])->pluck('id')->all()
        );
    }
}
