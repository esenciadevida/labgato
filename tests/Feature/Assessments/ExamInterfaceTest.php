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
use Tests\TestCase;

class ExamInterfaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Assessment $assessment;

    /**
     * question_id => correct option_id.
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

        foreach ([['easy', 2], ['intermediate', 2]] as [$difficulty, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $question = Question::create([
                    'question_category_id' => $hematology->id,
                    'question_type' => 'text',
                    'stem' => "Stem {$difficulty} number {$i}",
                    'difficulty' => $difficulty,
                ]);
                $this->addOptions($question);
            }
        }

        $assessment = Assessment::create(array_merge([
            'title' => 'CBT Interface Challenge',
            'slug' => 'cbt-interface-challenge',
            'description' => 'A professional CBT experience.',
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 90,
            'total_questions' => 4,
            'max_attempts' => 1,
            'allow_practice' => true,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'fullscreen_required' => true,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ], $overrides));

        AssessmentBlueprint::create([
            'assessment_id' => $assessment->id,
            'question_category_id' => $hematology->id,
            'question_count' => 4,
            'easy_percent' => 50,
            'intermediate_percent' => 50,
            'advanced_percent' => 0,
        ]);

        return $assessment;
    }

    private function addOptions(Question $question): void
    {
        foreach (['A', 'B', 'C', 'D'] as $index => $label) {
            $option = QuestionOption::create([
                'question_id' => $question->id,
                'label' => $label,
                'text' => "Option {$label} for {$question->id}",
                'is_correct' => $label === 'B',
                'sort_order' => $index,
            ]);

            if ($option->is_correct) {
                $this->correctOptions[$question->id] = $option->id;
            }
        }
    }

    private function makeAttempt(User $user, ?Assessment $assessment = null, array $overrides = []): ExamAttempt
    {
        $questions = Question::orderBy('id')->get()->pluck('id')->map(intval(...))->values();

        $optionOrders = [];
        foreach ($questions as $questionId) {
            $optionOrders[$questionId] = QuestionOption::where('question_id', $questionId)
                ->orderBy('sort_order')
                ->pluck('id')
                ->map(intval(...))
                ->all();
        }

        return ExamAttempt::create(array_merge([
            'user_id' => $user->id,
            'assessment_id' => ($assessment ?? $this->assessment)->id,
            'attempt_number' => 1,
            'mode' => 'official',
            'status' => 'in_progress',
            'question_ids' => $questions->all(),
            'option_orders' => $optionOrders,
            'started_at' => now(),
            'ends_at' => now()->addMinutes(90),
            'integrity_status' => 'normal',
        ], $overrides));
    }

    public function test_intro_shows_rules_and_begin_for_open_assessment(): void
    {
        $response = $this->actingAs($this->user)->get(route('assessments.show', $this->assessment));

        $response->assertOk()
            ->assertSee('Examination Rules')
            ->assertSee('one attempt only', false)
            ->assertSee('auto-submits at zero', false)
            ->assertSee('Subject Distribution')
            ->assertSee('Begin Examination')
            ->assertSee(route('assessments.start', $this->assessment));
    }

    public function test_intro_disables_begin_when_assessment_closed(): void
    {
        $closed = $this->makeAssessmentWorld([
            'title' => 'Closed Exam',
            'slug' => 'closed-exam',
            'status' => 'closed',
        ]);

        $response = $this->actingAs($this->user)->get(route('assessments.show', $closed));

        $response->assertOk()
            ->assertSee('not open right now', false);

        $content = $response->getContent();

        $this->assertStringContainsString('<button type="button" disabled', $content);
    }

    public function test_intro_redirects_to_exam_when_attempt_in_progress(): void
    {
        $attempt = $this->makeAttempt($this->user);

        $this->actingAs($this->user)
            ->get(route('assessments.show', $this->assessment))
            ->assertRedirect(route('exams.show', $attempt));
    }

    public function test_exam_page_renders_frozen_question_order_with_display_letters(): void
    {
        [$first, $second] = Question::orderBy('id')->limit(2)->get();

        // Freeze a deliberately REVERSED order to prove the page follows the frozen set.
        $attempt = $this->makeAttempt($this->user, overrides: [
            'question_ids' => [(int) $second->id, (int) $first->id],
        ]);

        $response = $this->actingAs($this->user)->get(route('exams.show', $attempt));

        $response->assertOk()
            ->assertSeeInOrder([$second->stem, $first->stem], false)
            ->assertSeeInOrder(['"letter":"A"', '"letter":"B"', '"letter":"C"', '"letter":"D"'], false);
    }

    public function test_exam_page_renders_image_questions(): void
    {
        $imageQuestion = Question::firstOrFail();
        $imageQuestion->forceFill([
            'question_type' => 'text_image',
            'image_path' => 'questions/interface-test/sample.png',
            'image_alt' => 'Blood smear',
        ])->save();

        $attempt = $this->makeAttempt($this->user);

        $this->actingAs($this->user)
            ->get(route('exams.show', $attempt))
            ->assertOk()
            ->assertSee('sample.png', false)
            ->assertSee('Blood smear', false);
    }

    public function test_navigator_counts_reflect_saved_answers_and_flags(): void
    {
        $attempt = $this->makeAttempt($this->user);
        $questions = $attempt->question_ids;

        $this->actingAs($this->user)->postJson(route('exams.answers.save', $attempt), [
            'question_id' => $questions[0],
            'option_id' => (int) $attempt->optionIdsFor($questions[0])[1],
        ])->assertOk();

        $this->actingAs($this->user)->postJson(route('exams.flag', $attempt), [
            'question_id' => $questions[1],
        ])->assertOk();

        $content = $this->actingAs($this->user)
            ->get(route('exams.show', $attempt))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('answeredCount()">1</span>', $content);
        $this->assertStringContainsString('flaggedCount()">1</span>', $content);
        $this->assertStringContainsString('unansweredCount()">3</span>', $content);
    }

    public function test_timer_is_seeded_from_server_remaining_seconds(): void
    {
        $this->freezeTime();

        $attempt = $this->makeAttempt($this->user, overrides: [
            'ends_at' => now()->addMinutes(90),
        ]);

        $expectedSeconds = (int) now()->diffInSeconds($attempt->ends_at->copy());

        $this->actingAs($this->user)
            ->get(route('exams.show', $attempt))
            ->assertOk()
            ->assertSee('data-initial-left="' . $expectedSeconds . '"', false);
    }

    public function test_selecting_an_option_through_the_interface_persists(): void
    {
        $attempt = $this->makeAttempt($this->user);
        $questionId = $attempt->question_ids[0];
        $optionId = (int) $attempt->optionIdsFor($questionId)[2];

        $this->actingAs($this->user)
            ->postJson(route('exams.answers.save', $attempt), [
                'question_id' => $questionId,
                'option_id' => $optionId,
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->assertDatabaseHas('exam_answers', [
            'exam_attempt_id' => $attempt->id,
            'question_id' => $questionId,
            'selected_option_id' => $optionId,
        ]);
    }

    public function test_submit_flow_shows_locked_results_on_submitted_page(): void
    {
        $attempt = $this->makeAttempt($this->user);

        $this->actingAs($this->user)
            ->postJson(route('exams.submit', $attempt))
            ->assertOk()
            ->assertJson(['status' => 'submitted']);

        $this->actingAs($this->user)
            ->get(route('exams.show', $attempt))
            ->assertRedirect(route('exams.submitted', $attempt));

        $this->actingAs($this->user)
            ->get(route('exams.submitted', $attempt))
            ->assertOk()
            ->assertSee('Your examination has been submitted.')
            ->assertSee('Results will be revealed when LabGOTO officially launches.')
            ->assertSee(route('account.dashboard'))
            ->assertDontSee('View My Result');
    }

    public function test_user_cannot_open_another_users_exam_or_submitted_page(): void
    {
        $attempt = $this->makeAttempt($this->user);
        $intruder = User::factory()->member()->create();

        $this->actingAs($intruder)->get(route('exams.show', $attempt))->assertStatus(403);
        $this->actingAs($intruder)->get(route('exams.submitted', $attempt))->assertStatus(403);
    }
}
