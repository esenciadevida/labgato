<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\ResultsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Assessment $assessment;

    /**
     * question_id => correct option row.
     */
    protected array $correctOptions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->member()->create();
        $this->assessment = $this->makeWorld();
    }

    private function makeWorld(): Assessment
    {
        $hematology = QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );
        $chemistry = QuestionCategory::firstOrCreate(
            ['slug' => 'clinical-chemistry'],
            ['name' => 'Clinical Chemistry']
        );

        $imageQuestion = $this->makeQuestion($hematology, 'Hem easy q0', 'easy');
        $imageQuestion->forceFill([
            'question_type' => 'text_image',
            'image_path' => 'questions/results-test/sample.png',
            'image_alt' => 'Result smear',
            'explanation' => 'Image-based reasoning applies here.',
        ])->save();

        $plainQuestion = $this->makeQuestion($chemistry, 'Chem intermediate q0', 'intermediate', 'Glucose above 70 mg/dL defines normoglycemia.');

        $assessment = Assessment::create([
            'title' => 'Results Challenge',
            'slug' => 'results-challenge',
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 60,
            'total_questions' => 2,
            'max_attempts' => 1,
            'results_status' => 'locked',
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ]);

        AssessmentBlueprint::create([
            'assessment_id' => $assessment->id,
            'question_category_id' => $hematology->id,
            'question_count' => 1,
            'easy_percent' => 100,
            'intermediate_percent' => 0,
            'advanced_percent' => 0,
        ]);
        AssessmentBlueprint::create([
            'assessment_id' => $assessment->id,
            'question_category_id' => $chemistry->id,
            'question_count' => 1,
            'easy_percent' => 0,
            'advanced_percent' => 0,
            'intermediate_percent' => 100,
        ]);

        // Remember the intended pairing for helpers below.
        $this->pairing = [$imageQuestion->id, $plainQuestion->id];

        return $assessment;
    }

    protected array $pairing = [];

    private function makeQuestion(QuestionCategory $category, string $stem, string $difficulty, ?string $explanation = null): Question
    {
        $question = Question::create([
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'stem' => $stem,
            'difficulty' => $difficulty,
            'explanation' => $explanation ?? "Explainer for {$stem}.",
        ]);

        foreach (['A', 'B', 'C', 'D'] as $index => $label) {
            $option = QuestionOption::create([
                'question_id' => $question->id,
                'label' => $label,
                'text' => "{$stem} option {$label}",
                'is_correct' => $label === 'B',
                'sort_order' => $index,
            ]);

            if ($option->is_correct) {
                $this->correctOptions[$question->id] = $option;
            }
        }

        return $question;
    }

    private function makeAttempt(User $user, int $number = 1, string $status = 'submitted', ?int $score = null): ExamAttempt
    {
        $questionIds = $this->pairing;

        $optionOrders = [];
        foreach ($questionIds as $questionId) {
            $optionOrders[$questionId] = QuestionOption::where('question_id', $questionId)
                ->orderBy('sort_order')
                ->pluck('id')
                ->map(intval(...))
                ->all();
        }

        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $this->assessment->id,
            'attempt_number' => $number,
            'mode' => 'official',
            'status' => $status,
            'question_ids' => $questionIds,
            'option_orders' => $optionOrders,
            'started_at' => now()->subHour(),
            'ends_at' => now()->subMinute(),
            'submitted_at' => $status !== 'in_progress' ? now() : null,
            'score' => $score,
            'percentage' => $score !== null ? round($score / count($questionIds) * 100, 2) : null,
            'time_spent_seconds' => $status !== 'in_progress' ? 600 : null,
            'integrity_status' => 'normal',
        ]);

        foreach ($questionIds as $questionId) {
            $attempt->answers()->create(['question_id' => $questionId]);
        }

        return $attempt;
    }

    private function seedAnswers(ExamAttempt $attempt, bool $secondCorrect): void
    {
        [$firstId, $secondId] = $this->pairing;

        ExamAnswer::query()
            ->where('exam_attempt_id', $attempt->id)
            ->where('question_id', $firstId)
            ->update([
                'selected_option_id' => (int) $this->correctOptions[$firstId]->id,
                'is_correct' => true,
                'flagged' => true,
            ]);

        $wrongOptionId = QuestionOption::where('question_id', $secondId)
            ->where('is_correct', false)
            ->orderBy('sort_order')
            ->value('id');

        ExamAnswer::query()
            ->where('exam_attempt_id', $attempt->id)
            ->where('question_id', $secondId)
            ->update([
                'selected_option_id' => (int) $wrongOptionId,
                'is_correct' => false,
            ]);

        if ($secondCorrect) {
            ExamAnswer::query()
                ->where('exam_attempt_id', $attempt->id)
                ->where('question_id', $secondId)
                ->update([
                    'selected_option_id' => (int) $this->correctOptions[$secondId]->id,
                    'is_correct' => true,
                ]);
        }
    }

    public function test_result_page_is_locked_without_score_when_not_released(): void
    {
        $attempt = $this->makeAttempt($this->user, score: 2);
        $this->seedAnswers($attempt, secondCorrect: true);

        $response = $this->actingAs($this->user)->get(route('exams.result', $attempt));

        $response->assertOk()
            ->assertSee('Your result is securely recorded.')
            ->assertSee('Results will be revealed when LabGOTO officially launches.')
            ->assertSee($this->assessment->title)
            ->assertDontSee('2 / 2')
            ->assertDontSee('Subject Performance')
            ->assertDontSee('Question Review');
    }

    public function test_result_page_reveals_score_when_released(): void
    {
        $this->assessment->forceFill(['results_status' => 'available'])->save();

        $attempt = $this->makeAttempt($this->user, score: 1);
        $this->seedAnswers($attempt, secondCorrect: false);

        $this->actingAs($this->user)
            ->get(route('exams.result', $attempt))
            ->assertOk()
            ->assertSee('50%')
            ->assertSee('1 / 2 correct', false)
            ->assertSee('Subject Performance')
            ->assertSee('Question Review');
    }

    public function test_rank_computed_against_other_official_attempts(): void
    {
        $top = User::factory()->member()->create();
        $bottom = User::factory()->member()->create();

        $aTop = $this->makeAttempt($top, score: 2);
        $aMid = $this->makeAttempt($this->user, score: 1);
        $aBottom = $this->makeAttempt($bottom, score: 0);

        $service = app(ResultsService::class);

        $this->assertSame(1, $service->rank($aTop));
        $this->assertSame(2, $service->rank($aMid));
        $this->assertSame(3, $service->rank($aBottom));
    }

    public function test_top_percent_computed_from_rank_and_total(): void
    {
        $top = User::factory()->member()->create();
        $bottom = User::factory()->member()->create();

        $aTop = $this->makeAttempt($top, score: 2);
        $aMid = $this->makeAttempt($this->user, score: 1);
        $aBottom = $this->makeAttempt($bottom, score: 0);

        $service = app(ResultsService::class);

        $this->assertSame(33, $service->topPercent($aTop));
        $this->assertSame(67, $service->topPercent($aMid));
        $this->assertSame(100, $service->topPercent($aBottom));
    }

    public function test_subject_breakdown_counts_per_category(): void
    {
        $attempt = $this->makeAttempt($this->user, score: 1);
        $this->seedAnswers($attempt, secondCorrect: true);

        $breakdown = app(ResultsService::class)->subjectBreakdown($attempt);

        $this->assertSame(2, $breakdown->count());

        $hematology = $breakdown->firstWhere('name', 'Hematology');
        $chemistry = $breakdown->firstWhere('name', 'Clinical Chemistry');

        $this->assertSame([1, 1, 100], [$hematology['correct'], $hematology['total'], $hematology['percent']]);
        $this->assertSame([1, 1, 100], [$chemistry['correct'], $chemistry['total'], $chemistry['percent']]);
    }

    public function test_review_shows_letters_explanations_and_flags(): void
    {
        $attempt = $this->makeAttempt($this->user, score: 1);
        $this->seedAnswers($attempt, secondCorrect: false);

        $review = collect(app(ResultsService::class)->review($attempt));

        $hemReview = $review->first(fn ($item) => $item['subject'] === 'Hematology');
        $chemReview = $review->first(fn ($item) => $item['subject'] === 'Clinical Chemistry');

        // Correct answer B in both cases.
        $this->assertSame('B', $hemReview['your_option']['letter']);
        $this->assertTrue($hemReview['is_correct']);
        $this->assertTrue($hemReview['flagged']);
        $this->assertSame('B', $hemReview['correct_option']['letter']);

        $this->assertFalse($chemReview['is_correct']);
        $this->assertNotSame('B', $chemReview['your_option']['letter']);
        $this->assertSame('B', $chemReview['correct_option']['letter']);
        $this->assertSame('Glucose above 70 mg/dL defines normoglycemia.', $chemReview['explanation']);

        $letters = collect($chemReview['options'])->pluck('letter')->all();
        $this->assertSame(['A', 'B', 'C', 'D'], $letters);
    }

    public function test_review_includes_image_questions(): void
    {
        $this->assessment->forceFill(['results_status' => 'available'])->save();

        $attempt = $this->makeAttempt($this->user, score: 2);
        $this->seedAnswers($attempt, secondCorrect: true);

        $this->actingAs($this->user)
            ->get(route('exams.result', $attempt))
            ->assertOk()
            ->assertSee('sample.png', false)
            ->assertSee('Result smear', false)
            ->assertSee('Image-based reasoning applies here.', false);
    }

    public function test_my_results_lists_attempts_and_locks_view(): void
    {
        $attempt = $this->makeAttempt($this->user, score: 2);

        $content = $this->actingAs($this->user)
            ->get(route('assessments.my-results'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($this->assessment->title, $content);
        $this->assertStringContainsString('cursor-not-allowed', $content);
        $this->assertStringContainsString('Locked', $content);

        // Released rows expose the View link to the detail route.
        $releasedUrl = route('exams.result', $attempt);
        $this->assertStringNotContainsString($releasedUrl, $content);

        $this->assessment->forceFill(['results_status' => 'available'])->save();

        $this->actingAs($this->user)
            ->get(route('assessments.my-results'))
            ->assertOk()
            ->assertSee($releasedUrl);
    }

    public function test_cannot_view_another_users_result(): void
    {
        $attempt = $this->makeAttempt($this->user, score: 2);
        $intruder = User::factory()->member()->create();

        $this->actingAs($intruder)
            ->get(route('exams.result', $attempt))
            ->assertStatus(403);
    }

    public function test_unsubmitted_attempt_cannot_view_result(): void
    {
        $attempt = $this->makeAttempt($this->user, status: 'in_progress');

        $this->actingAs($this->user)
            ->get(route('exams.result', $attempt))
            ->assertRedirect(route('exams.show', $attempt));

        $abandoned = $this->makeAttempt($this->user, number: 2, status: 'abandoned');

        $this->actingAs($this->user)
            ->get(route('exams.result', $abandoned))
            ->assertStatus(404);
    }
}
