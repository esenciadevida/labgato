<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\LaunchSetting;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QuestionTestSeeder extends Seeder
{
    /**
     * Subject slug => questions drawn for the smoke-test assessment.
     * Sums to the configured total (20).
     */
    private const SMOKE_BLUEPRINT = [
        'clinical-chemistry' => 5,
        'hematology' => 5,
        'microbiology' => 4,
        'immunology' => 3,
        'other' => 3,
    ];

    private const SMOKE_TOTAL = 20;
    private const SMOKE_DURATION = 19;

    private const SURPLUS_PER_DIFFICULTY = 1;

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('Running the question engine seed in a production environment will insert demo questions and a demo member.');
        }

        $this->call(QuestionCategorySeeder::class);

        $assessment = $this->ensureAssessment();
        $this->ensureQuestionBank();
        $this->ensureDemoMember();

        $this->command->info('Question engine smoke test ready.');
        $this->command->info("Assessment: {$assessment->title} (slug: {$assessment->slug}, ".self::SMOKE_TOTAL.' questions, '.self::SMOKE_DURATION.' min)');
        $this->command->info('Demo member: '.config('labgoto.seed.exam_engine.member_email')
            .' / '.config('labgoto.seed.exam_engine.member_password'));
    }

    private function ensureAssessment(): Assessment
    {
        $assessment = Assessment::firstOrCreate(
            ['slug' => 'engine-smoke-test'],
            [
                'title' => 'Engine Smoke Test',
                'type' => 'challenge',
                'status' => 'open',
                'duration_minutes' => self::SMOKE_DURATION,
                'total_questions' => self::SMOKE_TOTAL,
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

        foreach (self::SMOKE_BLUEPRINT as $slug => $count) {
            $category = QuestionCategory::where('slug', $slug)->first();

            if ($category === null) {
                continue;
            }

            AssessmentBlueprint::firstOrCreate(
                ['assessment_id' => $assessment->id, 'question_category_id' => $category->id],
                [
                    'question_count' => $count,
                    'easy_percent' => 30,
                    'intermediate_percent' => 50,
                    'advanced_percent' => 20,
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
     * Grow a light question bank sized to the smoke-test blueprint, split by
     * difficulty exactly as the generation service resolves the ratios.
     */
    private function ensureQuestionBank(): void
    {
        foreach (self::SMOKE_BLUEPRINT as $slug => $total) {
            $category = QuestionCategory::where('slug', $slug)->first();

            if ($category === null) {
                continue;
            }

            $easy = (int) round($total * 0.30);
            $advanced = (int) round($total * 0.20);
            $intermediate = max(0, $total - $easy - $advanced);

            $this->fillDifficulty($category, 'easy', $easy);
            $this->fillDifficulty($category, 'intermediate', $intermediate);
            $this->fillDifficulty($category, 'advanced', $advanced);
        }
    }

    private function fillDifficulty(QuestionCategory $category, string $difficulty, int $count): void
    {
        $count += self::SURPLUS_PER_DIFFICULTY;

        for ($i = 1; $i <= $count; $i++) {
            $stem = 'Smoke '.$category->name.' — '.$difficulty.' question '.str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            $question = Question::firstOrCreate(
                ['stem' => $stem],
                [
                    'question_category_id' => $category->id,
                    'question_type' => 'text',
                    'difficulty' => $difficulty,
                    'status' => 'active',
                    'explanation' => 'The correct answer is option A.',
                ]
            );

            foreach ([['A', 0], ['B', 1], ['C', 2], ['D', 3]] as [$label, $sort]) {
                QuestionOption::firstOrCreate(
                    ['question_id' => $question->id, 'label' => $label],
                    [
                        'text' => 'Option '.$label,
                        'is_correct' => $sort === 0,
                        'sort_order' => $sort,
                    ]
                );
            }
        }
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
