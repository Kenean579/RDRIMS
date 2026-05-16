<?php

namespace Database\Seeders;

use App\Models\Expertise;
use Illuminate\Database\Seeder;

class ExpertiseSeeder extends Seeder
{
    public function run(): void
    {
        $expertiseList = [
            'Artificial Intelligence',
            'Machine Learning',
            'Deep Learning',
            'Natural Language Processing',
            'Computer Vision',
            'Data Science',
            'Big Data Analytics',
            'Cloud Computing',
            'Cybersecurity',
            'Blockchain Technology',
            'Internet of Things',
            'Software Engineering',
            'Mobile Application Development',
            'Climate Change Adaptation',
            'Renewable Energy',
            'Solar Energy',
            'Wind Energy',
            'Hydropower',
            'Environmental Science',
            'Water Resource Management',
            'Public Health',
            'Epidemiology',
            'Health Systems Strengthening',
            'Maternal and Child Health',
            'Nutrition',
            'Agriculture',
            'Crop Science',
            'Soil Science',
            'Agricultural Economics',
            'Food Security',
            'Economics',
            'Development Economics',
            'Macroeconomics',
            'Education',
            'Curriculum Development',
            'Gender Studies',
            'Sociology',
            'Psychology',
            'Disaster Risk Management',
            'Urban Planning',
            'Transportation Engineering',
            'Structural Engineering',
            'Material Science',
            'Biotechnology',
            'Pharmaceutical Sciences',
            'Mathematics',
            'Statistics',
        ];

        foreach ($expertiseList as $expertise) {
            Expertise::create(['name' => $expertise]);
        }
    }
}