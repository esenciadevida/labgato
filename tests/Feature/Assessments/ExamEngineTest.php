<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExamEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Assessment $assessment;

    /**
     * question_id => correct option_id (known ground truth for scoring tests).
     */
    protected array $correctOptions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->member()->create();

        $this->assessment = $this->makeAssessmentWorld();
    }

    private function makeAssessmentWorld(array $overrides = []): Assessment
    {
        $hematology = QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );
        $chemistry = QuestionCategory::firstOrCreate(
            ['slug' => 'clinical-chemistry'],
            ['name' => 'Clinical Chemistry']
        );

        // Hematology: 3 easy, 3 intermediate, 0 advanced (blueprint 50/50/0 of 6).
        foreach ([['easy', 3], ['intermediate', 3]] as [$difficulty, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $question = Question::create([
                    'question_category_id' => $hematology->id,
                    'question_type' => 'text',
                    'stem' => "Hem {$difficulty} q{$i}",
                    'difficulty' => $difficulty,
                ]);
                $this->addOptions($question);
            }
        }

        // Chemistry: 2 easy, 2 intermediate, 1 advanced available
        // (blueprint wants easy 1 / inter 2 / adv 1 of 4 → backfill uses the pool).
        foreach ([['easy', 2], ['intermediate', 2], ['advanced', 1]] as [$difficulty, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $question = Question::create([
                    'question_category_id' => $chemistry->id,
                    'question_type' => 'text',
                    'stem' => "Chem {$difficulty} q{$i}",
                    'difficulty' => $difficulty,
                ]);
                $this->addOptions($question);
            }
        }

        $assessment = Assessment::create(array_merge([
            'title' => 'ASCPI Challenge Test',
            'slug' => 'ascpi-challenge-test',
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 90,
            'total_questions' => 10,
            'max_attempts' => 1,
            'allow_practice' => true,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ], $overrides));

        AssessmentBlueprint::create([
            'assessment_id' => $assessment->id,
            'question_category_id' => $hematology->id,
            'question_count' => 6,
            'easy_percent' => 50,
            'intermediate_percent' => 50,
            'advanced_percent' => 0,
        ]);
        AssessmentBlueprint::create([
            'assessment_id' => $assessment->id,
            'question_category_id' => $chemistry->id,
            'question_count' => 4,
            'easy_percent' => 25,
            'advanced_percent' => 25,
            'intermediate_percent' => 50,
        ]);

        return $assessment;
    }

    private function addOptions(Question $question): void
    {
        foreach (['A', 'B', 'C', 'D'] as $index => $label) {
            $option = QuestionOption::create([
                'question_id' => $question->id,
                'label' => $label,
                'text' => "Option {$label}",
                'is_correct' => $label === 'B',
                'sort_order' => $index,
            ]);

            if ($option->is_correct) {
                $this->correctOptions[$question->id] = $option->id;
            }
        }
    }

    public function test_start_creates_attempt_with_blueprint_question_counts(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        $this->assertCount(10, $attempt->question_ids);
        $this->assertSame(10, $attempt->answers()->count());
        $this->assertSame('in_progress', $attempt->status);
        $this->assertSame(1, $attempt->attempt_number);

        $categoryIds = Question::whereIn('id', $attempt->question_ids)
            ->get(['id', 'question_category_id'])
            ->groupBy('question_category_id')
            ->map(fn ($g) => $g->count());

        $this->assertSame(6, $categoryIds[QuestionCategory::where('slug', 'hematology')->value('id')]);
        $this->assertSame(4, $categoryIds[QuestionCategory::where('slug', 'clinical-chemistry')->value('id')]);
    }

    public function test_difficulty_distribution_matches_blueprint_percentages(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        $difficulties = Question::whereIn('id', $attempt->question_ids)
            ->get(['id', 'question_category_id', 'difficulty']);

        $hemaId = QuestionCategory::where('slug', 'hematology')->value('id');
        $chemId = QuestionCategory::where('slug', 'clinical-chemistry')->value('id');

        $this->assertSame(3, $difficulties->where('question_category_id', $hemaId)->where('difficulty', 'easy')->count());
        $this->assertSame(3, $difficulties->where('question_category_id', $hemaId)->where('difficulty', 'intermediate')->count());

        // Chemistry blueprint: 25% easy (1), 50% intermediate (2), 25% advanced (1).
        $this->assertSame(1, $difficulties->where('question_category_id', $chemId)->where('difficulty', 'easy')->count());
        $this->assertGreaterThanOrEqual(1, $difficulties->where('question_category_id', $chemId)->where('difficulty', 'intermediate')->count());
        $this->assertGreaterThanOrEqual(1, $difficulties->where('question_category_id', $chemId)->where('difficulty', 'advanced')->count());
    }

    public function test_option_orders_frozen_and_contain_correct_option(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        foreach ($attempt->question_ids as $questionId) {
            $orders = $attempt->optionIdsFor($questionId);
            $options = QuestionOption::where('question_id', $questionId)->pluck('id');

            $this->assertCount(4, $orders);
            $this->assertEqualsCanonicalizing(
                $options->all(),
                array_map(intval(...), $orders)
            );

            $correctId = $this->correctOptions[$questionId];
            $this->assertContains((string) $correctId === '' ? $correctId : (int) $correctId, array_map(intval(...), $orders));
        }
    }

    public function test_repeat_start_returns_same_attempt_id(): void
    {
        $service = app(\App\Services\ExamGenerationService::class);

        $first = $service->startAttempt($this->user, $this->assessment);
        $second = $service->startAttempt($this->user, $this->assessment);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, ExamAttempt::where('user_id', $this->user->id)->count());
    }

    public function test_second_official_attempt_blocked_when_one_attempt_only(): void
    {
        $eligibility = app(\App\Services\ExamEligibilityService::class);

        $first = $eligibility->canStart($this->user, $this->assessment, 'official');
        $this->assertTrue($first['ok']);

        app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        // Even after submission the attempt is spent.
        $attempt = ExamAttempt::where('user_id', $this->user->id)->firstOrFail();
        $attempt->forceFill(['status' => 'submitted', 'submitted_at' => now()])->save();
        $this->correctOptions = [];

        $second = $eligibility->canStart($this->user, $this->assessment, 'official');

        $this->assertFalse($second['ok']);
        $this->assertSame('already_attempted', $second['reason']);
        $this->assertNotNull($second['redirect']);
        $this->assertSame($attempt->id, $second['redirect']->id);
    }

    public function test_practice_attempt_allowed_and_increments_attempt_number(): void
    {
        app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        $practice = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment, 'practice');

        $this->assertSame('practice', $practice->mode);
        $this->assertSame(2, $practice->attempt_number);
    }

    public function test_practice_blocked_when_disabled(): void
    {
        $closedPractice = $this->makeAssessmentWorld([
            'title' => 'No Practice',
            'slug' => 'no-practice',
            'allow_practice' => false,
        ]);

        $check = app(\App\Services\ExamEligibilityService::class)->canStart($this->user, $closedPractice, 'practice');

        $this->assertFalse($check['ok']);
        $this->assertSame('practice_disabled', $check['reason']);
    }

    public function test_save_answer_updates_answer_row(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        $questionId = $attempt->question_ids[0];
        $optionId = $attempt->optionIdsFor($questionId)[1];

        $response = $this->actingAs($this->user)
            ->postJson("/exam/{$attempt->id}/answers", [
                'question_id' => $questionId,
                'option_id' => $optionId,
            ]);

        $response->assertOk()->assertJson(['saved' => true]);

        $answer = $attempt->answers()->where('question_id', $questionId)->firstOrFail();
        $this->assertSame((int) $optionId, $answer->selected_option_id);
        $this->assertNotNull($answer->answered_at);
    }

    public function test_save_answer_rejects_option_outside_frozen_order(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        // An option from a DIFFERENT question is never in this question's frozen order.
        $foreignOptionId = QuestionOption::query()
            ->whereNotIn('question_id', [$attempt->question_ids[0]])
            ->value('id');

        $this->actingAs($this->user)
            ->postJson("/exam/{$attempt->id}/answers", [
                'question_id' => $attempt->question_ids[0],
                'option_id' => (int) $foreignOptionId,
            ])
            ->assertStatus(422);
    }

    public function test_flag_toggles_on_and_off(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        $questionId = $attempt->question_ids[0];

        $this->actingAs($this->user)
            ->postJson("/exam/{$attempt->id}/flag", ['question_id' => $questionId])
            ->assertOk()
            ->assertJson(['question_id' => (int) $questionId, 'flagged' => true]);

        $this->actingAs($this->user)
            ->postJson("/exam/{$attempt->id}/flag", ['question_id' => $questionId])
            ->assertOk()
            ->assertJson(['question_id' => (int) $questionId, 'flagged' => false]);
    }

    public function test_submit_computes_score_and_percentage(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        $questionIds = $attempt->question_ids;

        // Answer 7 questions; 5 correct (option B), 2 wrong.
        foreach (array_slice($questionIds, 0, 7) as $i => $questionId) {
            $allowed = $attempt->optionIdsFor($questionId);
            $wrongOption = collect($allowed)->first(fn ($id) => (int) $id !== (int) $this->correctOptions[$questionId]);

            $optionId = $i < 5
                ? $this->correctOptions[$questionId]
                : $wrongOption;

            $this->actingAs($this->user)->postJson("/exam/{$attempt->id}/answers", [
                'question_id' => $questionId,
                'option_id' => (int) $optionId,
            ])->assertOk();
        }

        $response = $this->actingAs($this->user)->postJson("/exam/{$attempt->id}/submit");

        $response->assertOk()->assertJson([
            'status' => 'submitted',
            'score' => 5,
            'total_questions' => 10,
            'percentage' => 50.0,
        ]);
    }

    public function test_ends_at_equals_started_at_plus_duration(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        $this->assertSame(
            $attempt->started_at->timestamp + ($this->assessment->duration_minutes * 60),
            $attempt->ends_at->timestamp
        );
    }

    public function test_autosubmit_command_scores_expired_attempts(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        // Pre-answer one correct question while the timer is live.
        $this->actingAs($this->user)->postJson("/exam/{$attempt->id}/answers", [
            'question_id' => $attempt->question_ids[0],
            'option_id' => (int) $this->correctOptions[$attempt->question_ids[0]],
        ])->assertOk();

        // Expire the attempt on the server clock.
        $attempt->forceFill(['ends_at' => now()->subMinute()])->save();

        Artisan::call('exam:autosubmit');

        $attempt->refresh();
        $this->assertSame('auto_submitted', $attempt->status);
        $this->assertSame(1, $attempt->score);
        $this->assertSame(10.0, (float) $attempt->percentage);

        // A second expired attempt with no answers scores zero.
        $other = User::factory()->member()->create();
        $lateAttempt = app(\App\Services\ExamGenerationService::class)->startAttempt($other, $this->assessment);
        $lateAttempt->forceFill(['ends_at' => now()->subSeconds(30)])->save();

        Artisan::call('exam:autosubmit');

        $lateAttempt->refresh();
        $this->assertSame('auto_submitted', $lateAttempt->status);
        $this->assertSame(0, $lateAttempt->score);
        $this->assertSame(0.0, (float) $lateAttempt->percentage);
    }

    public function test_user_cannot_access_or_save_on_another_users_attempt(): void
    {
        $attempt = app(\App\Services\ExamGenerationService::class)->startAttempt($this->user, $this->assessment);

        $intruder = User::factory()->member()->create();

        $questionId = $attempt->question_ids[0];

        $this->actingAs($intruder)->get("/exam/{$attempt->id}")->assertStatus(403);

        $this->actingAs($intruder)->postJson("/exam/{$attempt->id}/answers", [
            'question_id' => $questionId,
            'option_id' => (int) $attempt->optionIdsFor($questionId)[0],
        ])->assertStatus(403);

        $this->actingAs($intruder)->postJson("/exam/{$attempt->id}/flag", [
            'question_id' => $questionId,
        ])->assertStatus(403);

        $this->actingAs($intruder)->postJson("/exam/{$attempt->id}/submit")->assertStatus(403);
    }
}
