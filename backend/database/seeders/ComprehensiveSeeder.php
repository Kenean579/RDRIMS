<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\University;
use App\Models\Campus;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\ThematicArea;
use App\Models\Call;
use App\Models\CallStatus;
use App\Models\Proposal;
use App\Models\ProposalStatus;
use App\Models\ProposalReviewer;
use App\Models\ReviewDecision;
use App\Models\Notification;
use App\Models\Event;
use App\Models\AuditLog;
use App\Models\File;
use Faker\Factory as Faker;

class ComprehensiveSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $now = Carbon::now();

        // 1. Academic Hierarchy
        $university = University::updateOrCreate(
            ['name' => 'Wollo University'],
            [
                'code' => 'WU'
            ]
        );

        $campus = Campus::updateOrCreate(
            ['name' => 'Dessie Campus'],
            [
                'university_id' => $university->id,
                'code' => 'DC'
            ]
        );

        $facultiesData = [
            'Faculty of Computing' => ['code' => 'FC', 'depts' => ['Software Engineering' => 'SE', 'Computer Science' => 'CS', 'Information Systems' => 'IS']],
            'Faculty of Engineering' => ['code' => 'FE', 'depts' => ['Civil Engineering' => 'CE', 'Mechanical Engineering' => 'ME', 'Electrical Engineering' => 'EE']],
            'Faculty of Business' => ['code' => 'FB', 'depts' => ['Management' => 'MGT', 'Accounting' => 'ACCT', 'Economics' => 'ECON']],
            'Faculty of Health Sciences' => ['code' => 'FHS', 'depts' => ['Nursing' => 'NURS', 'Public Health' => 'PH', 'Pharmacy' => 'PHARM']],
            'Faculty of Natural Sciences' => ['code' => 'FNS', 'depts' => ['Physics' => 'PHYS', 'Chemistry' => 'CHEM', 'Biology' => 'BIO', 'Mathematics' => 'MATH']]
        ];

        $departments = [];
        $faculties = [];
        foreach ($facultiesData as $facultyName => $facultyData) {
            $faculty = Faculty::updateOrCreate(
                ['name' => $facultyName],
                [
                    'campus_id' => $campus->id,
                    'code' => $facultyData['code']
                ]
            );
            $faculties[] = $faculty;

            foreach ($facultyData['depts'] as $deptName => $deptCode) {
                $departments[] = Department::updateOrCreate(
                    ['name' => $deptName],
                    [
                        'faculty_id' => $faculty->id,
                        'code' => $deptCode
                    ]
                );
            }
        }

        // 2. Thematic Areas
        $themeNames = [
            'Artificial Intelligence',
            'Healthcare Informatics',
            'Digital Agriculture',
            'Climate Change',
            'Renewable Energy',
            'Education Technology',
            'Cybersecurity',
            'Water Resource Management',
            'Public Health Policy',
            'Maternal and Child Health',
            'Food Security',
            'Smart Cities',
            'Poverty Alleviation',
            'Indigenous Knowledge Systems',
            'Green Economy',
            'Infectious Diseases',
            'Blockchain Technology',
            'Data Science',
            'Nanotechnology',
            'Structural Engineering'
        ];

        $themes = [];
        foreach ($themeNames as $themeName) {
            $themes[] = ThematicArea::updateOrCreate(
                ['name' => $themeName],
                ['description' => "Research in $themeName"]
            );
        }

        // 3. Users
        $roles = Role::pluck('id', 'name')->toArray();
        $password = Hash::make('password');

        $usersCreated = [];

        // Super Admin (1)
        $usersCreated['super_admin'][] = User::updateOrCreate(
            ['email' => 'superadmin@wollo.edu.et'],
            ['name' => 'Super Administrator', 'password' => $password, 'department_id' => $departments[0]->id, 'is_active' => true]
        );

        // Research Admin (2)
        for ($i = 1; $i <= 2; $i++) {
            $usersCreated['research_admin'][] = User::updateOrCreate(
                ['email' => "researchadmin{$i}@wollo.edu.et"],
                ['name' => $faker->name, 'password' => $password, 'department_id' => $departments[0]->id, 'is_active' => true]
            );
        }

        // Faculty Admin (5)
        for ($i = 0; $i < 5; $i++) {
            $usersCreated['faculty_admin'][] = User::updateOrCreate(
                ['email' => "facultyadmin{$i}@wollo.edu.et"],
                ['name' => $faker->name, 'password' => $password, 'department_id' => $departments[array_rand($departments)]->id, 'is_active' => true]
            );
        }

        // Department Heads (10)
        for ($i = 0; $i < 10; $i++) {
            $usersCreated['department_head'][] = User::updateOrCreate(
                ['email' => "depthead{$i}@wollo.edu.et"],
                ['name' => $faker->name, 'password' => $password, 'department_id' => $departments[$i % count($departments)]->id, 'is_active' => true]
            );
        }

        // Reviewers (15)
        for ($i = 1; $i <= 15; $i++) {
            $usersCreated['reviewer'][] = User::updateOrCreate(
                ['email' => "reviewer{$i}@wollo.edu.et"],
                ['name' => $faker->name, 'password' => $password, 'department_id' => $departments[array_rand($departments)]->id, 'is_active' => true]
            );
        }

        // Researchers (50)
        for ($i = 1; $i <= 50; $i++) {
            $usersCreated['researcher'][] = User::updateOrCreate(
                ['email' => "researcher{$i}@wollo.edu.et"],
                ['name' => $faker->name, 'password' => $password, 'department_id' => $departments[array_rand($departments)]->id, 'is_active' => true]
            );
        }

        // Assign Roles
        foreach ($usersCreated as $roleName => $userList) {
            if (isset($roles[$roleName])) {
                foreach ($userList as $user) {
                    if (!$user->roles->contains($roles[$roleName])) {
                        $user->roles()->attach($roles[$roleName], ['assigned_by' => $usersCreated['super_admin'][0]->id, 'assigned_at' => now()]);
                    }
                }
            }
        }

        // 4. Calls for Proposals
        $callStatuses = CallStatus::pluck('id', 'name')->toArray();
        $calls = [];

        // Active (10)

        // Closed (5)
        for ($i = 1; $i <= 5; $i++) {
            $calls[] = Call::create([
                'title' => "Closed Call $i: " . $faker->sentence(4),
                'description' => $faker->paragraph,
                'deadline' => $now->copy()->subDays(rand(10, 30)),
                'thematic_areas' => implode(', ', $faker->randomElements($themeNames, rand(2, 5))),
                'created_by' => $usersCreated['research_admin'][0]->id,
                'status_id' => $callStatuses['closed'] ?? 2,
                'university_id' => $university->id
            ]);
        }

        // Draft (5)
        for ($i = 1; $i <= 5; $i++) {
            $calls[] = Call::create([
                'title' => "Draft Call $i: " . $faker->sentence(4),
                'description' => $faker->paragraph,
                'deadline' => $now->copy()->addDays(rand(30, 60)),
                'thematic_areas' => implode(', ', $faker->randomElements($themeNames, rand(2, 5))),
                'created_by' => $usersCreated['research_admin'][0]->id,
                'status_id' => $callStatuses['draft'] ?? 3,
                'university_id' => $university->id
            ]);
        }

        // 5. Proposals
        $proposalStatuses = ProposalStatus::pluck('id', 'name')->toArray();
        $proposalTypes = \App\Models\ProposalType::pluck('id')->toArray();
        $statusKeys = array_keys($proposalStatuses);
        $proposals = [];

        for ($i = 1; $i <= 100; $i++) {
            $statusName = $faker->randomElement($statusKeys);
            $researcher = $usersCreated['researcher'][array_rand($usersCreated['researcher'])];

            $proposals[] = Proposal::create([
                'call_id' => $calls[array_rand($calls)]->id,
                'title' => "Proposal $i: " . $faker->sentence(5),
                'abstract' => $faker->paragraph(3),
                'objectives' => $faker->paragraph,
                'methodology' => $faker->paragraph,
                'budget' => rand(50000, 500000),
                'status_id' => $proposalStatuses[$statusName],
                'type_id' => $faker->randomElement($proposalTypes) ?? 1,
                'keywords' => implode(', ', $faker->words(3)),
                'submitted_by' => $researcher->id,
                'submitted_at' => $now->copy()->subDays(rand(1, 30)),
                'academic_year_id' => 1
            ]);
        }

        // 6. Proposal Reviews
        $reviewDecisions = ReviewDecision::pluck('id', 'name')->toArray();
        $decisionKeys = array_keys($reviewDecisions);

        // Filter submitted/under review proposals
        $reviewableProposals = array_filter($proposals, function ($p) use ($proposalStatuses) {
            return $p->status_id !== ($proposalStatuses['draft'] ?? 0);
        });

        $reviewCount = 0;
        foreach ($reviewableProposals as $proposal) {
            if ($reviewCount >= 200)
                break;

            // Pick 2-3 random reviewers not equal to submitter
            $assignedReviewers = collect($usersCreated['reviewer'])
                ->filter(fn($r) => $r->id !== $proposal->submitted_by)
                ->random(rand(2, 3));

            foreach ($assignedReviewers as $reviewer) {
                if ($reviewCount >= 200)
                    break;

                $decision = $faker->randomElement($decisionKeys);
                $proposal->reviewers()->attach($reviewer->id, [
                    'assigned_by' => $usersCreated['research_admin'][0]->id,
                    'assigned_at' => $now->copy()->subDays(rand(5, 10)),
                    'submitted_at' => $now->copy()->subDays(rand(1, 4)),
                    'overall_score' => rand(50, 100),
                    'overall_comments' => $faker->paragraph,
                    'decision_id' => $reviewDecisions[$decision] ?? 1
                ]);
                $reviewCount++;
            }
        }

        // 7. Notifications
        for ($i = 1; $i <= 300; $i++) {
            Notification::create([
                'user_id' => $usersCreated['researcher'][array_rand($usersCreated['researcher'])]->id,
                'message' => $faker->paragraph,
                'type' => $faker->randomElement(['info', 'success', 'warning']),
                'priority' => $faker->randomElement(['low', 'medium', 'high']),
                'read_at' => $faker->boolean(50) ? $now->copy()->subDays(rand(0, 30)) : null,
                'created_at' => $now->copy()->subDays(rand(0, 30))
            ]);
        }

        // 8. Announcements (Events)
        for ($i = 1; $i <= 20; $i++) {
            Event::create([
                'title' => "Announcement: " . $faker->sentence,
                'description' => $faker->paragraph,
                'start_date' => $now->copy()->addDays(rand(-10, 20)),
                'end_date' => $now->copy()->addDays(rand(21, 30)),
                'type' => 'announcement',
                'created_by' => $usersCreated['research_admin'][0]->id,
                'university_id' => $university->id
            ]);
        }

        // 9. Documents
        for ($i = 1; $i <= 50; $i++) {
            File::create([
                'file_path' => "files/document_{$i}.pdf",
                'original_filename' => "document_{$i}.pdf",
                'mime_type' => 'application/pdf',
                'uploaded_by' => $usersCreated['researcher'][0]->id,
                'is_public' => false,
                'version' => 1,
            ]);
        }

        // 10. Audit Logs
        for ($i = 1; $i <= 500; $i++) {
            AuditLog::create([
                'user_id' => $usersCreated['researcher'][array_rand($usersCreated['researcher'])]->id,
                'action' => $faker->randomElement(['login', 'create', 'update', 'view']),
                'table_name' => $faker->randomElement(['users', 'proposals', 'calls', 'events']),
                'record_id' => rand(1, 100),
                'ip_address' => $faker->ipv4,
                'created_at' => $now->copy()->subDays(rand(0, 30))
            ]);
        }

    }
}
