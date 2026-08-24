<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\QuestionCategory;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    private const BLUEPRINT = [
        'hematology' => 20,
        'clinical-chemistry' => 20,
        'microbiology' => 20,
        'blood-banking-transfusion' => 15,
        'immunology' => 15,
        'other' => 10,
    ];

    public function run(): void
    {
        $assessment = Assessment::firstOrCreate(
            ['slug' => 'ascpi-challenge-2026'],
            [
                'title' => 'ASCPI Challenge 2026',
                'type' => 'challenge',
                'status' => 'scheduled',
                'duration_minutes' => 150,
                'total_questions' => 100,
                'max_attempts' => 1,
                'allow_practice' => true,
                'certificate_enabled' => true,
                'pass_percentage' => 70,
                'results_status' => 'locked',
                'answer_review_status' => 'locked',
                'leaderboard_status' => 'locked',
            ]
        );

        foreach (self::BLUEPRINT as $slug => $count) {
            $category = QuestionCategory::where('slug', $slug)->first();

            if ($category === null) {
                continue;
            }

            AssessmentBlueprint::firstOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'question_category_id' => $category->id,
                ],
                [
                    'question_count' => $count,
                    'easy_percent' => 30,
                    'intermediate_percent' => 50,
                    'advanced_percent' => 20,
                ]
            );
        }
    }
}
