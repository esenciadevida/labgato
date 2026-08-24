<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\User;
use Database\Seeders\AssessmentSeeder;
use Database\Seeders\QuestionCategorySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_categories_and_ascpi_assessment_with_blueprint(): void
    {
        $this->seed(QuestionCategorySeeder::class);
        $this->seed(AssessmentSeeder::class);

        $this->assertSame(7, QuestionCategory::count());

        $assessment = Assessment::where('slug', 'ascpi-challenge-2026')->firstOrFail();

        $this->assertSame('challenge', $assessment->type);
        $this->assertSame('scheduled', $assessment->status);
        $this->assertSame(150, $assessment->duration_minutes);
        $this->assertSame(100, $assessment->total_questions);
        $this->assertSame('70.00', (string) $assessment->pass_percentage);

        $blueprints = $assessment->blueprints;

        $this->assertCount(6, $blueprints);
        $this->assertSame(100, $assessment->blueprintQuestionCount());
    }

    public function test_question_with_four_options_exposes_correct_option(): void
    {
        $category = QuestionCategory::create(['name' => 'Hematology', 'slug' => 'hematology']);

        $question = Question::create([
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'stem' => 'Which cell is the primary oxygen carrier?',
            'difficulty' => 'easy',
        ]);

        foreach (['A', 'B', 'C', 'D'] as $index => $label) {
            $question->options()->create([
                'label' => $label,
                'text' => "Option {$label}",
                'is_correct' => $label === 'B',
                'sort_order' => $index,
            ]);
        }

        $question->load('options');

        $this->assertCount(4, $question->options);
        $this->assertNotNull($question->correctOption);
        $this->assertSame('B', $question->correctOption->label);
    }

    public function test_attempt_creation_stores_frozen_question_ids(): void
    {
        $user = User::factory()->member()->create();
        $assessment = Assessment::create([
            'title' => 'ASCPI Challenge 2026',
            'slug' => 'ascpi-challenge-2026',
        ]);

        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 1,
            'status' => 'in_progress',
            'question_ids' => [5, 3, 9, 1],
            'option_orders' => [5 => [21, 22], 3 => [11, 12]],
            'started_at' => now(),
            'ends_at' => now()->addMinutes(150),
        ]);

        $attempt->refresh();

        $this->assertSame([5, 3, 9, 1], $attempt->question_ids);
        $this->assertSame([21, 22], $attempt->option_orders[5]);
    }

    public function test_attempt_user_assessment_and_number_must_be_unique(): void
    {
        $user = User::factory()->member()->create();
        $assessment = Assessment::create(['title' => 'Test', 'slug' => 'test']);

        $base = [
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 1,
        ];

        ExamAttempt::create($base + ['started_at' => now()]);

        $this->expectException(QueryException::class);

        ExamAttempt::create($base + ['started_at' => now()]);
    }

    public function test_exam_answers_are_unique_per_attempt_and_question(): void
    {
        $user = User::factory()->member()->create();
        $assessment = Assessment::create(['title' => 'Test', 'slug' => 'test-two']);
        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
        ]);
        $category = QuestionCategory::create(['name' => 'Microbiology', 'slug' => 'microbiology']);
        $question = Question::create([
            'question_category_id' => $category->id,
            'stem' => 'Stem',
        ]);

        ExamAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'selected_option_id' => null,
        ]);

        $this->expectException(QueryException::class);

        ExamAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'selected_option_id' => null,
        ]);
    }

    public function test_assessment_is_open_respects_status_and_window(): void
    {
        $assessment = Assessment::create([
            'title' => 'Windowed',
            'slug' => 'windowed',
            'status' => 'open',
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addHour(),
        ]);

        $this->assertTrue($assessment->isOpen());

        $this->travel(2)->hours();

        $this->assertFalse($assessment->fresh()->isOpen());

        $closed = Assessment::create(['title' => 'Closed', 'slug' => 'closed', 'status' => 'scheduled']);

        $this->assertFalse($closed->isOpen());
    }

    public function test_attempt_expiry_requires_in_progress_status(): void
    {
        $user = User::factory()->member()->create();
        $assessment = Assessment::create(['title' => 'Expiry', 'slug' => 'expiry']);

        $expired = ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 1,
            'status' => 'in_progress',
            'ends_at' => now()->subMinutes(10),
        ]);

        $submitted = ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 2,
            'status' => 'submitted',
            'ends_at' => now()->subMinutes(10),
        ]);

        $this->assertTrue($expired->isExpired());
        $this->assertFalse($submitted->isExpired());
        $this->assertTrue($expired->remainingSeconds() === 0);
    }

    public function test_valid_certificate_scope_excludes_revoked(): void
    {
        $user = User::factory()->member()->create();
        $assessment = Assessment::create(['title' => 'Certs', 'slug' => 'certs']);
        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 1,
        ]);
        $revokedAttempt = ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 2,
        ]);

        Certificate::create([
            'user_id' => $user->id,
            'exam_attempt_id' => $attempt->id,
            'assessment_id' => $assessment->id,
            'certificate_number' => 'LGT-2026-00001',
            'verification_code' => 'ABCDEFGH23',
            'type' => 'achievement',
            'score_percent' => 82.00,
            'rank_at_issue' => 1,
            'issued_at' => now(),
        ]);

        Certificate::create([
            'user_id' => $user->id,
            'exam_attempt_id' => $revokedAttempt->id,
            'assessment_id' => $assessment->id,
            'certificate_number' => 'LGT-2026-00002',
            'verification_code' => 'JKLMNPQR56',
            'type' => 'participation',
            'score_percent' => 90.00,
            'issued_at' => now(),
            'revoked_at' => now(),
        ]);

        $this->assertSame(1, Certificate::valid()->count());
        $this->assertSame('LGT-2026-00001', Certificate::valid()->first()->certificate_number);
        $this->assertSame(2, $user->certificates()->count());
        $this->assertSame($attempt->id, $user->certificates()->valid()->first()->exam_attempt_id);
    }
}
