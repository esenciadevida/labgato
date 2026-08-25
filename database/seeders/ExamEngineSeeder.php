<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\LaunchSetting;
use App\Models\QuestionCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExamEngineSeeder extends Seeder
{
    /**
     * Subject slug => questions drawn for the blueprinted challenge.
     *
     * Mirrors the distribution used by AssessmentSeeder so the generation
     * service can satisfy every blueprint row.
     */
    private const BLUEPRINT = [
        'hematology' => 9,
        'clinical-chemistry' => 9,
        'microbiology' => 9,
        'blood-banking-transfusion' => 9,
        'immunology' => 9,
        'other' => 9,
    ];

    private const TOTAL_QUESTIONS = 54;

    /**
     * Extra active questions per difficulty per subject, so the engine has
     * headroom when it picks at random.
     */
    private const SURPLUS_PER_DIFFICULTY = 1;

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('Running the exam engine seed in a production environment will insert demo questions and a demo member.');
        }

        $this->call(QuestionCategorySeeder::class);

        \App\Support\ExamQuestionBank::seed();

        $assessment = $this->ensureAssessment();
        $this->ensureDemoMember();

        $this->command->info('Exam engine test data ready.');
        $this->command->info("Assessment: {$assessment->title} (slug: {$assessment->slug})");
        $this->command->info('Demo member: '.config('labgoto.seed.exam_engine.member_email')
            .' / '.config('labgoto.seed.exam_engine.member_password'));
    }

    /**
     * Ensure the challenge exists, is open, that the launch gate is open and
     * that results / review / leaderboard are released so the full post-exam
     * lifecycle can be exercised.
     */
    private function ensureAssessment(): Assessment
    {
        $assessment = Assessment::firstOrCreate(
            ['slug' => 'ascpi-challenge-2026'],
            [
                'title' => 'ASCPI Challenge 2026',
                'type' => 'challenge',
                'status' => 'open',
                'duration_minutes' => 150,
                'total_questions' => self::TOTAL_QUESTIONS,
                'max_attempts' => 1,
                'allow_practice' => true,
                'certificate_enabled' => true,
                'pass_percentage' => 70,
                'results_status' => 'available',
                'answer_review_status' => 'available',
                'leaderboard_status' => 'available',
                'opens_at' => null,
                'closes_at' => null,
            ]
        );

        foreach (self::BLUEPRINT as $slug => $count) {
            $category = QuestionCategory::where('slug', $slug)->first();

            if ($category === null) {
                continue;
            }

            AssessmentBlueprint::firstOrCreate(
                ['assessment_id' => $assessment->id, 'question_category_id' => $category->id],
                [
                    'question_count' => $count,
                    'easy_percent' => 30,
                    'intermediate_percent' => 40,
                    'advanced_percent' => 30,
                ]
            );
        }

        $assessment->update([
            'status' => 'open',
            'opens_at' => null,
            'closes_at' => null,
            'results_status' => 'available',
            'answer_review_status' => 'available',
            'leaderboard_status' => 'available',
        ]);

        LaunchSetting::query()->update([
            'challenge_open' => true,
            'results_status' => 'available',
            'answer_review_status' => 'available',
            'leaderboard_status' => 'available',
        ]);
        LaunchSetting::flushCache();

        return $assessment->fresh();
    }

    /**
     * Grow the active question bank to cover every blueprint row, split by
     * difficulty exactly as the generation service resolves the ratios.
     */
    private function ensureQuestionBank(): void
    {
        \App\Support\ExamQuestionBank::seed();
    }

    private function ensureDemoMember(): void
    {
        $email = strtolower((string) config('labgoto.seed.exam_engine.member_email'));

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Demo Member',
                'password' => Hash::make((string) config('labgoto.seed.exam_engine.member_password')),
                'password_set_at' => now(),
                'email_verified_at' => now(),
                'is_active' => true,
                'user_type' => 'member',
            ]
        );

        $user->saveProfile(['professional_status' => 'professional']);
    }
}
