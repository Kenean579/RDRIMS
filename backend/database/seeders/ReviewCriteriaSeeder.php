<?php

namespace Database\Seeders;

use App\Models\ReviewCriterion;
use Illuminate\Database\Seeder;

class ReviewCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            ['name' => 'Originality', 'description' => 'Novelty and uniqueness of the research idea.', 'max_score' => 10, 'is_active' => true],
            ['name' => 'Feasibility', 'description' => 'Can the research be completed with proposed resources and timeline?', 'max_score' => 10, 'is_active' => true],
            ['name' => 'Methodology', 'description' => 'Soundness and appropriateness of the research methods.', 'max_score' => 10, 'is_active' => true],
            ['name' => 'Impact', 'description' => 'Potential societal, academic, or economic impact.', 'max_score' => 10, 'is_active' => true],
            ['name' => 'Budget Justification', 'description' => 'Is the budget reasonable and well-justified?', 'max_score' => 10, 'is_active' => true],
            ['name' => 'Literature Review', 'description' => 'Comprehensiveness and relevance of the literature review.', 'max_score' => 10, 'is_active' => true],
            ['name' => 'Ethical Considerations', 'description' => 'Adequacy of ethical safeguards and data protection measures.', 'max_score' => 10, 'is_active' => true],
            ['name' => 'Team Qualification', 'description' => 'Qualifications and track record of the research team.', 'max_score' => 10, 'is_active' => true],
        ];
        foreach ($criteria as $criterion) {
            ReviewCriterion::firstOrCreate($criterion);
        }
    }
}
