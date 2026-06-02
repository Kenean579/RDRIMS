<?php

namespace Database\Seeders;

use App\Models\ReviewCriterion;
use Illuminate\Database\Seeder;

class ReviewCriterionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criteria = [
            [
                'name' => 'Originality',
                'description' => 'Novelty and uniqueness of the research idea. Does it address a knowledge gap?',
                'max_score' => 10,
                'weight' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Feasibility',
                'description' => 'Can the research be completed with the proposed resources, timeline, and methodology?',
                'max_score' => 10,
                'weight' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Methodology',
                'description' => 'Soundness, appropriateness, and rigor of the research design and methods.',
                'max_score' => 10,
                'weight' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Impact',
                'description' => 'Potential societal, academic, economic, or policy impact of the research findings.',
                'max_score' => 10,
                'weight' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Budget Justification',
                'description' => 'Is the requested budget reasonable, well-justified, and aligned with the activities?',
                'max_score' => 10,
                'weight' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Literature Review',
                'description' => 'Comprehensiveness, currency, and relevance of the literature review and theoretical framework.',
                'max_score' => 10,
                'weight' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Ethical Considerations',
                'description' => 'Adequacy of ethical safeguards, informed consent, and data protection measures.',
                'max_score' => 10,
                'weight' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Team Qualification',
                'description' => 'Qualifications, experience, and track record of the research team.',
                'max_score' => 10,
                'weight' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($criteria as $criterion) {
            ReviewCriterion::create($criterion);
        }
    }
}
