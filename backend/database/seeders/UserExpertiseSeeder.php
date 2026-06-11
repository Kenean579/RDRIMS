<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Expertise;
use Illuminate\Database\Seeder;

class UserExpertiseSeeder extends Seeder
{
    public function run(): void
    {
        $assign = function ($email, ...$expertiseNames) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->expertise()->sync(
                    Expertise::whereIn('name', $expertiseNames)->pluck('id')->toArray()
                );
            }
        };

        $assign('tigist.researcher@wollo.edu.et', 'Artificial Intelligence', 'Machine Learning', 'Natural Language Processing', 'Deep Learning');
        $assign('henok.researcher@wollo.edu.et', 'Renewable Energy', 'Solar Energy');
        $assign('yonas.reviewer@wollo.edu.et', 'Artificial Intelligence', 'Data Science', 'Cybersecurity', 'Machine Learning');
        $assign('frehiwot.reviewer@wollo.edu.et', 'Climate Change Adaptation', 'Environmental Science', 'Water Resource Management');
        $assign('daniel.reviewer@wollo.edu.et', 'Material Science', 'Structural Engineering', 'Transportation Engineering');
        $assign('sara.researcher@wollo.edu.et', 'Public Health', 'Epidemiology', 'Maternal and Child Health', 'Nutrition');
    }
}
