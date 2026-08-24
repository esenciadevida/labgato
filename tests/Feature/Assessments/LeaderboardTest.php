<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\LaunchSetting;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Role;
use App\Models\User;
use App\Services\ExamScoringService;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    private LeaderboardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LeaderboardService::class);
    }

    private function makeAssessment(string $suffix, string $leaderboardStatus = 'locked'): Assessment
    {
        return Assessment::create([
            'title' => 'Leaderboard Challenge '.$suffix,
            'slug' => 'leaderboard-challenge-'.$suffix,
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 60,
            'total_questions' => 10,
            'max_attempts' => 1,
            'results_status' => 'locked',
            'leaderboard_status' => $leaderboardStatus,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ]);
    }

    private function makeAttempt(
        User $user,
        Assessment $assessment,
        int $number = 1,
        string $status = 'submitted',
        ?int $score = null,
        ?float $percentage = null,
        ?int $timeSpent = null,
        array $questionIds = []
    ): ExamAttempt {
        static $sequence = 0;
        $sequence++;

        return ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => $number,
            'mode' => 'official',
            'status' => $status,
            'question_ids' => $questionIds,
            'option_orders' => [],
            'started_at' => now()->subHour(),
            'ends_at' => now()->addMinutes(30),
            'submitted_at' => $status !== 'in_progress' ? now()->addSeconds($sequence) : null,
            'score' => $score,
            'percentage' => $percentage,
            'time_spent_seconds' => $timeSpent,
            'integrity_status' => 'normal',
        ]);
    }

    public function test_board_orders_by_score_desc_then_time_asc(): void
    {
        $assessment = $this->makeAssessment('order');

        $slowWinner = User::factory()->member()->create();
        $fastRunnerUp = User::factory()->member()->create();
        $lowScorer = User::factory()->member()->create();
        $topScorer = User::factory()->member()->create();

        $this->makeAttempt($slowWinner, $assessment, score: 10, percentage: 100, timeSpent: 500);
        $this->makeAttempt($fastRunnerUp, $assessment, score: 10, percentage: 100, timeSpent: 300);
        $this->makeAttempt($lowScorer, $assessment, score: 8, percentage: 80, timeSpent: 100);
        $this->makeAttempt($topScorer, $assessment, score: 12, percentage: 120, timeSpent: 900);

        $board = collect($this->service->board($assessment));

        $this->assertSame(
            [$topScorer->id, $fastRunnerUp->id, $slowWinner->id, $lowScorer->id],
            $board->pluck('user_id')->all()
        );
        $this->assertSame([1, 2, 3, 4], $board->pluck('rank')->all());
    }

    public function test_equal_scores_break_tie_by_faster_time(): void
    {
        $assessment = $this->makeAssessment('tiebreak');

        $slower = User::factory()->member()->create();
        $faster = User::factory()->member()->create();

        $this->makeAttempt($slower, $assessment, score: 9, percentage: 90, timeSpent: 700);
        $this->makeAttempt($faster, $assessment, score: 9, percentage: 90, timeSpent: 240);

        $board = collect($this->service->board($assessment));

        $this->assertSame([$faster->id, $slower->id], $board->pluck('user_id')->all());
        $this->assertSame(1, $this->service->position($this->service->board($assessment), $faster->id));
    }

    public function test_opted_out_member_appears_as_anonymous(): void
    {
        $assessment = $this->makeAssessment('privacy', 'available');
        $privateUser = User::factory()->member()->create();
        $privateUser->profile()->updateOrCreate([], ['show_on_leaderboard' => false]);
        $publicUser = User::factory()->member()->create();

        $this->makeAttempt($privateUser, $assessment, score: 10, percentage: 100, timeSpent: 200);
        $this->makeAttempt($publicUser, $assessment, score: 5, percentage: 50, timeSpent: 400);

        $board = collect($this->service->board($assessment));
        $privateRow = $board->firstWhere('user_id', $privateUser->id);

        $this->assertSame('Anonymous', $privateRow['name']);
        $this->assertTrue($privateRow['anonymous']);

        $content = $this->actingAs($publicUser)
            ->get(route('leaderboard.show', $assessment))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Anonymous', $content);
        $this->assertStringNotContainsString($privateUser->name, $content);
    }

    public function test_locked_board_hides_rows_from_members_but_not_super_admins(): void
    {
        $assessment = $this->makeAssessment('lockgate', 'locked');
        $participant = User::factory()->member()->create();
        $this->makeAttempt($participant, $assessment, score: 10, percentage: 100, timeSpent: 200);

        $this->actingAs(User::factory()->member()->create())
            ->get(route('leaderboard.show', $assessment))
            ->assertOk()
            ->assertSee('Leaderboard will be revealed when LabGOTO launches.')
            ->assertDontSee($participant->name);

        $role = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $admin = User::factory()->create();
        $admin->roles()->attach($role->id);

        $this->actingAs($admin)
            ->get(route('leaderboard.show', $assessment))
            ->assertOk()
            ->assertSee($participant->name);
    }

    public function test_all_time_aggregates_best_percent_average_and_entries(): void
    {
        $first = $this->makeAssessment('agg-a', 'available');
        $second = $this->makeAssessment('agg-b', 'available');

        $alice = User::factory()->member()->create();
        $alice->profile()->updateOrCreate([], ['display_name' => 'Alice Chen']);
        $bob = User::factory()->member()->create();

        // Alice on first: two attempts, only the best (90) counts.
        $this->makeAttempt($alice, $first, number: 1, score: 5, percentage: 50, timeSpent: 600);
        $this->makeAttempt($alice, $first, number: 2, status: 'auto_submitted', score: 9, percentage: 90, timeSpent: 300);
        // Alice on second: single attempt.
        $this->makeAttempt($alice, $second, score: 7, percentage: 70, timeSpent: 500);
        // Bob only took the first assessment.
        $this->makeAttempt($bob, $first, score: 6, percentage: 60, timeSpent: 800);

        $allTime = collect($this->service->allTime());

        $aliceRow = $allTime->firstWhere('user_id', $alice->id);
        $bobRow = $allTime->firstWhere('user_id', $bob->id);

        $this->assertSame('Alice Chen', $aliceRow['name']);
        $this->assertSame(80, $aliceRow['score']);
        $this->assertSame(2, $aliceRow['entries']);
        $this->assertSame(1, $aliceRow['rank']);

        $this->assertSame(60, $bobRow['score']);
        $this->assertSame(1, $bobRow['entries']);
        $this->assertSame(2, $bobRow['rank']);
        $this->assertSame($bob->name, $bobRow['name']);
    }

    public function test_scoring_a_new_attempt_updates_the_cached_board(): void
    {
        $category = QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );
        $question = Question::create([
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'stem' => 'Cache invalidation stem',
            'difficulty' => 'easy',
            'explanation' => 'Because.',
        ]);
        $correctOption = QuestionOption::create([
            'question_id' => $question->id,
            'label' => 'A',
            'text' => 'Correct answer',
            'is_correct' => true,
            'sort_order' => 0,
        ]);

        $assessment = $this->makeAssessment('invalidate');
        $user = User::factory()->member()->create();

        $attempt = $this->makeAttempt($user, $assessment, status: 'in_progress', questionIds: [$question->id]);
        $attempt->answers()->create(['question_id' => $question->id]);

        // Warm the cache before any completed attempt exists.
        $this->assertSame([], $this->service->board($assessment));

        ExamAnswer::query()
            ->where('exam_attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->update(['selected_option_id' => $correctOption->id]);

        app(ExamScoringService::class)->score($attempt);

        $board = collect($this->service->board($assessment));

        $this->assertCount(1, $board);
        $this->assertSame($user->id, $board[0]['user_id']);
        $this->assertSame(1, $board[0]['score']);
    }

    public function test_page_for_math(): void
    {
        $this->assertSame(1, $this->service->pageFor(1));
        $this->assertSame(1, $this->service->pageFor(25));
        $this->assertSame(1, $this->service->pageFor(50));
        $this->assertSame(2, $this->service->pageFor(51));
        $this->assertSame(2, $this->service->pageFor(100));
        $this->assertSame(3, $this->service->pageFor(101));
        $this->assertSame(4, $this->service->pageFor(175, 50));
        $this->assertSame(2, $this->service->pageFor(11, 10));
    }

    public function test_index_lists_only_boards_visible_to_members(): void
    {
        $visible = $this->makeAssessment('visible', 'available');
        $hidden = $this->makeAssessment('hidden', 'locked');

        $viewer = User::factory()->member()->create();
        $participant = User::factory()->member()->create();

        $this->makeAttempt($participant, $visible, score: 8, percentage: 80, timeSpent: 300);
        $this->makeAttempt($participant, $hidden, score: 9, percentage: 90, timeSpent: 300);

        // While launch is locked, only per-assessment overrides surface a chip.
        $content = $this->actingAs($viewer)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('/leaderboard/'.$visible->slug, $content);
        $this->assertStringNotContainsString('/leaderboard/'.$hidden->slug, $content);

        // Once launch unlocks globally, every board is listed and the
        // all-time aggregate renders.
        LaunchSetting::query()->update(['leaderboard_status' => 'available']);
        LaunchSetting::flushCache();

        $this->actingAs($viewer)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('Avg Best %')
            ->assertSee('/leaderboard/'.$hidden->slug, false);
    }
}
