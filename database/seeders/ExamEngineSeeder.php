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

class ExamEngineSeeder extends Seeder
{
    /**
     * Subject slug => questions drawn for the blueprinted challenge.
     *
     * Mirrors the distribution used by AssessmentSeeder so the generation
     * service can satisfy every blueprint row.
     */
    private const BLUEPRINT = [
        'hematology' => 20,
        'clinical-chemistry' => 20,
        'microbiology' => 20,
        'blood-banking-transfusion' => 15,
        'immunology' => 15,
        'other' => 10,
    ];

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

        $assessment = $this->ensureAssessment();
        $this->ensureQuestionBank();
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
                'total_questions' => 100,
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
     * Grow the active question bank to cover every blueprint row, split by
     * difficulty exactly as the generation service resolves the ratios.
     */
    private function ensureQuestionBank(): void
    {
        foreach (self::BLUEPRINT as $slug => $total) {
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
            $stem = 'Demo '.$category->name.' — '.$difficulty.' question '.str_pad((string) $i, 3, '0', STR_PAD_LEFT);

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
