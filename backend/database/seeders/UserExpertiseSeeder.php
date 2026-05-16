<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Expertise;
use Illuminate\Database\Seeder;

class UserExpertiseSeeder extends Seeder
{
    public function run(): void
    {
        // Dr. Tigist Haile (id=3) -> AI, ML, NLP
        $user = User::where('email', 'tigist.researcher@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Artificial Intelligence', 'Machine Learning', 'Natural Language Processing', 'Deep Learning',
                ])->pluck('id')->toArray()
            );
        }

        // Dr. Henok Tesfaye (id=4) -> Renewable Energy, Solar Energy
        $user = User::where('email', 'henok.researcher@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Renewable Energy', 'Solar Energy', 'Physics',
                ])->pluck('id')->toArray()
            );
        }

        // Dr. Sara Mohammed (id=5) -> Public Health, Epidemiology
        $user = User::where('email', 'sara.researcher@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Public Health', 'Epidemiology', 'Maternal and Child Health', 'Nutrition',
                ])->pluck('id')->toArray()
            );
        }

        // Prof. Yonas Mulugeta (id=6) -> AI, Data Science, Cybersecurity
        $user = User::where('email', 'yonas.reviewer@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Artificial Intelligence', 'Data Science', 'Cybersecurity', 'Machine Learning',
                ])->pluck('id')->toArray()
            );
        }

        // Dr. Frehiwot Assefa (id=7) -> Climate Change, Environmental Science
        $user = User::where('email', 'frehiwot.reviewer@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Climate Change Adaptation', 'Environmental Science', 'Water Resource Management',
                ])->pluck('id')->toArray()
            );
        }

        // Dr. Daniel Bekele (id=8) -> Material Science, Structural Engineering
        $user = User::where('email', 'daniel.reviewer@wollo.edu.et')->first();
        if ($user) {
            $user->expertise()->sync(
                Expertise::whereIn('name', [
                    'Material Science', 'Structural Engineering', 'Transportation Engineering',
                ])->pluck('id')->toArray()
            );
        }
    }
}