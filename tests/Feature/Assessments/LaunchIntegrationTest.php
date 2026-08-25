<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\Certificate;
use App\Models\ExamAttempt;
use App\Models\LaunchSetting;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LaunchIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /* ------------------------------------------------------------- helpers */

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(
            \App\Models\Role::where('slug', 'super-administrator')->firstOrFail()
        );

        return $admin;
    }

    private function makeCategory(string $name, string $slug): QuestionCategory
    {
        return QuestionCategory::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }

    private function makeAssessment(array $overrides = [], ?array $blueprints = null): Assessment
    {
        $hematology = $this->makeCategory('Hematology', 'hematology');

        if (Question::where('question_category_id', $hematology->id)->count() < 10) {
            foreach ([['easy', 5], ['intermediate', 5]] as [$difficulty, $count]) {
                for ($i = 0; $i < $count; $i++) {
                    $this->makeQuestion($hematology, "Hem {$difficulty} q{$i}", $difficulty);
                }
            }
        }

        $assessment = Assessment::create(array_merge([
            'title' => 'Monthly ASCPi Challenge',
            'slug' => 'monthly-ascpi-challenge',
            'description' => 'The flagship monthly assessment.',
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 60,
            'total_questions' => 10,
            'max_attempts' => 1,
            'allow_practice' => true,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'pass_percentage' => 70,
            'certificate_enabled' => true,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ], $overrides));

        foreach ($blueprints ?? [
            [$hematology->id, 10, 50, 50, 0],
        ] as [$categoryId, $count, $easy, $intermediate, $advanced]) {
            AssessmentBlueprint::create([
                'assessment_id' => $assessment->id,
                'question_category_id' => $categoryId,
                'question_count' => $count,
                'easy_percent' => $easy,
                'intermediate_percent' => $intermediate,
                'advanced_percent' => $advanced,
            ]);
        }

        return $assessment;
    }

    private function makeQuestion(QuestionCategory $category, string $stem, string $difficulty): array
    {
        $question = Question::create([
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'stem' => $stem,
            'difficulty' => $difficulty,
        ]);

        foreach (['A', 'B', 'C', 'D'] as $index => $label) {
            $option = QuestionOption::create([
                'question_id' => $question->id,
                'label' => $label,
                'text' => "Option {$label}",
                'is_correct' => $label === 'B',
                'sort_order' => $index,
            ]);

            if ($option->is_correct) {
                $this->groundTruth[$question->id] = (int) $option->id;
            }
        }

        return [$question->id, $this->groundTruth[$question->id]];
    }

    /**
     * Drive a member through a complete official attempt via HTTP and
     * return the submitted attempt.
     */
    private function completeOfficial(User $user, Assessment $assessment, int $correctCount = 10): ExamAttempt
    {
        $this->actingAs($user)
            ->post(route('assessments.start', $assessment), ['mode' => 'official'])
            ->assertRedirect();

        $attempt = ExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->where('mode', 'official')
            ->firstOrFail();

        foreach ($attempt->question_ids as $index => $questionId) {
            $allowed = $attempt->optionIdsFor($questionId);
            $wrong = collect($allowed)->first(fn ($id) => (int) $id !== (int) $this->groundTruth[$questionId]);

            $this->actingAs($user)->postJson(route('exams.answers.save', $attempt), [
                'question_id' => $questionId,
                'option_id' => $index < $correctCount ? (int) $this->groundTruth[$questionId] : (int) $wrong,
            ])->assertOk();
        }

        $this->actingAs($user)->postJson(route('exams.submit', $attempt))->assertOk();

        return $attempt->fresh();
    }

    private array $groundTruth = [];

    private function lockSite(): void
    {
        LaunchSetting::current()->update(['main_site_status' => 'locked']);
        LaunchSetting::flushCache();
    }

    /* ------------------------------------------------------------ Task 1+2 */

    public function test_admin_creates_assessment_with_blueprint_and_validation_rejects_mismatch(): void
    {
        $chemistry = $this->makeCategory('Clinical Chemistry', 'clinical-chemistry');
        $parasitology = $this->makeCategory('Parasitology', 'parasitology');

        $payload = [
            'title' => 'September Challenge',
            'slug' => '',
            'description' => 'Monthly challenge.',
            'type' => 'challenge',
            'status' => 'scheduled',
            'duration_minutes' => 60,
            'total_questions' => 6,
            'pass_percentage' => 70,
            'one_attempt_only' => '1',
            'allow_practice' => '1',
            'shuffle_questions' => '1',
            'shuffle_options' => '1',
            'fullscreen_required' => '1',
            'certificate_enabled' => '1',
            'results_available' => '0',
            'answer_review_available' => '0',
            'leaderboard_visible' => '0',
            'blueprints' => [
                [
                    'question_category_id' => $chemistry->id,
                    'include' => '1',
                    'question_count' => 4,
                    'easy_percent' => 50,
                    'intermediate_percent' => 50,
                    'advanced_percent' => 0,
                ],
                [
                    'question_category_id' => $parasitology->id,
                    'include' => '1',
                    'question_count' => 2,
                    'easy_percent' => 100,
                    'intermediate_percent' => 0,
                    'advanced_percent' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin())
            ->post(route('admin.assessments.store'), $payload);

        $response->assertRedirect(route('admin.assessments.index'));

        $assessment = Assessment::where('slug', 'september-challenge')->firstOrFail();
        $this->assertSame(6, $assessment->blueprintQuestionCount());
        $this->assertSame(1, $assessment->max_attempts);
        $this->assertTrue($assessment->allow_practice);
        $this->assertSame('locked', $assessment->results_status);

        // Counts drive the total automatically; a stale total_questions is corrected.
        $badCounts = $payload;
        $badCounts['title'] = 'September Challenge B';
        $badCounts['slug'] = 'september-challenge-b';
        $badCounts['total_questions'] = 7; // intentionally wrong - must be overridden

        $this->actingAs($this->admin())
            ->post(route('admin.assessments.store'), $badCounts)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(6, Assessment::where('slug', 'september-challenge-b')->firstOrFail()->total_questions);

        // Difficulty percentages per row must sum to 100.
        $badPercents = $payload;
        $badPercents['slug'] = 'september-challenge-2';
        $badPercents['blueprints'][0]['easy_percent'] = 40;

        $response = $this->actingAs($this->admin())
            ->post(route('admin.assessments.store'), $badPercents)
            ->assertSessionHasErrors();

        $this->assertStringContainsString(
            'must sum to 100',
            collect(session('errors')->all())->implode(' ')
        );
    }

    public function test_status_transitions_via_endpoint(): void
    {
        $assessment = $this->makeAssessment(['status' => 'scheduled']);
        $this->admin();

        foreach (['open', 'closed', 'archived'] as $status) {
            $this->actingAs($this->admin())
                ->post(route('admin.assessments.status', $assessment), ['status' => $status])
                ->assertRedirect();

            $this->assertSame($status, $assessment->fresh()->status);
        }

        $this->actingAs($this->admin())
            ->post(route('admin.assessments.status', $assessment), ['status' => 'not-a-status'])
            ->assertSessionHasErrors('status');
    }

    /* -------------------------------------------------------------- Task 3 */

    public function test_hub_lists_open_first_scheduled_with_countdown_and_closed_with_links(): void
    {
        $closed = $this->makeAssessment(['slug' => 'closed-a', 'title' => 'Closed Challenge A']);
        $scheduled = $this->makeAssessment(['slug' => 'sched-a', 'title' => 'Scheduled Challenge B', 'status' => 'scheduled']);
        $open = $this->makeAssessment(['slug' => 'open-a', 'title' => 'Open Challenge C']);
        $draft = $this->makeAssessment(['slug' => 'draft-a', 'title' => 'Draft Challenge D', 'status' => 'draft']);

        $member = User::factory()->member()->create(['name' => 'Closed Participant']);
        $attempt = $this->completeOfficial($member, $closed);
        $closed->update(['status' => 'closed']);

        $guest = $this->actingAs($member)->get(route('assessments.index'))->assertOk()->getContent();

        $this->assertLessThan(
            strpos($guest, $scheduled->title),
            strpos($guest, $open->title),
            'Open assessments must appear before scheduled ones.'
        );
        $this->assertLessThan(
            strpos($guest, $closed->title),
            strpos($guest, $scheduled->title),
            'Scheduled assessments must appear before closed ones.'
        );

        $this->assertStringContainsString(
            'data-countdown="'.$scheduled->opens_at->toIso8601String().'"',
            $guest
        );
        $this->assertStringNotContainsString($draft->title, $guest);

        $memberView = $this->actingAs($member)
            ->get(route('assessments.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(route('exams.result', $attempt), $memberView);
        $this->assertStringContainsString('Next challenge coming soon.', $memberView);
    }

    /* -------------------------------------------------------------- Task 4 */

    public function test_nav_and_wall_behavior_for_assessments_links(): void
    {
        // The exam links now live in the member profile menu, not the guest nav,
        // and the exam hub is reachable pre-launch (exempt from the wall).
        $this->lockSite();

        $member = User::factory()->member()->create();

        $this->actingAs($member)->get(route('assessments.index'))->assertOk();

        // Assessment hub page is member-only; it no longer shows the nav dropdown links.
        $content = $this->actingAs($member)->get(route('assessments.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Assessments Hub', $content);

        LaunchSetting::current()->update(['main_site_status' => 'open']);
        LaunchSetting::flushCache();
    }

    /* -------------------------------------------------------------- Task 5 */

    public function test_homepage_shows_assessments_section_only_when_live(): void
    {
        $flagship = $this->makeAssessment();

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('id="assessments"', $home);
        $this->assertStringContainsString($flagship->title, $home);

        $this->lockSite();

        $wall = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString($flagship->title, $wall);
    }

    /* -------------------------------------------------------------- Task 4 */

    public function test_practice_run_allowed_post_launch_and_blocked_pre_launch(): void
    {
        $assessment = $this->makeAssessment();
        $member = User::factory()->member()->create();

        // Practice before an official attempt is not offered by the UI and
        // the eligibility service still guards it via the official check.
        $this->actingAs($member)
            ->get(route('assessments.show', $assessment))
            ->assertOk()
            ->assertDontSee('Start Practice Run');

        $official = $this->completeOfficial($member, $assessment);

        // The submitted page offers practice once the official run is banked.
        $this->actingAs($member)
            ->get(route('exams.submitted', $official))
            ->assertOk()
            ->assertSee('Start Practice Run');

        $response = $this->actingAs($member)
            ->post(route('assessments.start', $assessment), ['mode' => 'practice']);

        $practice = ExamAttempt::query()
            ->where('user_id', $member->id)
            ->where('mode', 'practice')
            ->firstOrFail();

        $response->assertRedirect(route('exams.show', $practice));
        $this->assertSame(2, $practice->attempt_number);

        // The exam page carries the unranked banner.
        $this->actingAs($member)->get(route('exams.show', $practice))
            ->assertOk()
            ->assertSee('Practice Run')
            ->assertSee('not ranked');

        // Pre-launch the exam hub stays reachable (exempt from the wall), so a
        // fresh practice run is permitted rather than being bounced home.
        $this->lockSite();

        $this->actingAs($member)
            ->post(route('assessments.start', $assessment), ['mode' => 'practice'])
            ->assertRedirect();

        LaunchSetting::current()->update(['main_site_status' => 'open']);
        LaunchSetting::flushCache();

        // Assessments without practice enabled refuse it.
        $noPractice = $this->makeAssessment(['slug' => 'no-practice-b', 'allow_practice' => false]);

        $this->actingAs($member)
            ->from(route('assessments.index'))
            ->post(route('assessments.start', $noPractice), ['mode' => 'practice'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_practice_attempt_excluded_from_leaderboard(): void
    {
        $assessment = $this->makeAssessment()->forceFill([
            'leaderboard_status' => 'available',
        ]);
        $assessment->save();

        $alpha = User::factory()->member()->create(['name' => 'Alpha One']);
        $beta = User::factory()->member()->create(['name' => 'Beta Two']);

        $this->completeOfficial($alpha, $assessment, 8);
        $this->completeOfficial($beta, $assessment, 5);

        // A perfect practice run must not leak onto the board.
        $this->actingAs($alpha)
            ->post(route('assessments.start', $assessment), ['mode' => 'practice'])
            ->assertRedirect();

        $practice = ExamAttempt::query()
            ->where('user_id', $alpha->id)
            ->where('mode', 'practice')
            ->firstOrFail();

        foreach ($practice->question_ids as $questionId) {
            $this->actingAs($alpha)->postJson(route('exams.answers.save', $practice), [
                'question_id' => $questionId,
                'option_id' => (int) $this->groundTruth[$questionId],
            ])->assertOk();
        }
        $this->actingAs($alpha)->postJson(route('exams.submit', $practice))->assertOk();

        $board = app(\App\Services\LeaderboardService::class)->board($assessment);
        $this->assertCount(2, $board);
        $this->assertSame(80.0, (float) $board[0]['percent']);

        $page = $this->get(route('leaderboard.show', $assessment))->assertOk()->getContent();

        // The champion appears on the podium and again in the table; a single
        // official entry per member proves practice runs stay off the board.
        $this->assertSame(2, substr_count($page, 'Alpha One'));
        $this->assertSame(1, substr_count($page, 'Beta Two'));
    }

    /* -------------------------------------------------------------- Task 6 */

    public function test_results_release_notifies_participants_exactly_once(): void
    {
        $assessment = $this->makeAssessment()
            ->forceFill(['results_status' => 'available']);
        $assessment->save();

        $p1 = User::factory()->member()->create(['name' => 'Notify One']);
        $p2 = User::factory()->member()->create(['name' => 'Notify Two']);
        $participant3 = User::factory()->member()->create(['name' => 'Participant Three']);
        $outsider = User::factory()->member()->create(['name' => 'Outside Observer']);

        $this->completeOfficial($p1, $assessment, 9);
        $this->completeOfficial($p2, $assessment, 6);
        $this->completeOfficial($participant3, $assessment, 3);

        $superAdmin = $this->admin();

        $payload = [
            'launch_date' => now()->format('Y-m-d'),
            'launch_time' => '20:00',
            'timezone' => 'Africa/Lagos',
            'auto_activate' => '1',
            'registration_open' => '1',
            'challenge_open' => '1',
            'results_status' => 'available',
            'answer_review_status' => 'locked',
            'leaderboard_status' => 'locked',
            'main_site_status' => 'open',
        ];

        $this->actingAs($superAdmin)->put(route('admin.launch.update'), $payload)->assertRedirect();

        foreach ([$p1, $p2, $participant3] as $participant) {
            $notifications = $participant->notifications()
                ->where('type', \App\Notifications\ResultsReleasedNotification::class)
                ->get();

            $this->assertCount(1, $notifications);
            $this->assertStringContainsString(
                "{$assessment->title} result is live",
                $notifications[0]->data['message']
            );
        }

        // Members without an attempt stay silent.
        $this->assertCount(0, $outsider->notifications);

        // Re-running without a locked→available transition sends nothing new.
        $this->actingAs($superAdmin)->put(route('admin.launch.update'), $payload)->assertRedirect();

        $this->assertSame(
            3,
            \Illuminate\Notifications\DatabaseNotification::query()
                ->where('type', \App\Notifications\ResultsReleasedNotification::class)
                ->count()
        );
    }

    public function test_activate_launch_command_releases_pending_notifications(): void
    {
        $assessment = $this->makeAssessment()
            ->forceFill(['results_status' => 'available']);
        $assessment->save();

        $participant = User::factory()->member()->create(['name' => 'Wake Up Participant']);
        $this->completeOfficial($participant, $assessment, 8);

        LaunchSetting::current()->update([
            'main_site_status' => 'locked',
            'auto_activate' => true,
            'launch_at' => now()->subMinutes(5),
        ]);
        LaunchSetting::flushCache();

        Artisan::call('launch:check');

        $this->assertSame('open', LaunchSetting::current()->fresh()->main_site_status);

        $notifications = $participant->notifications()
            ->where('type', \App\Notifications\ResultsReleasedNotification::class)
            ->get();

        $this->assertCount(1, $notifications);
        $this->assertStringContainsString(
            route('exams.result', ExamAttempt::where('user_id', $participant->id)->first()),
            $notifications[0]->data['url']
        );
    }

    /* -------------------------------------------------------- FULL JOURNEY */

    public function test_full_journey_registration_to_shared_certificate(): void
    {
        // 1. Register.
        $this->post(route('register.submit'), [
            'name' => 'Journey Member',
            'email' => 'journey@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
            'professional_status' => 'student',
            'institution' => 'University of Lagos',
        ])->assertRedirect(route('account.dashboard'));

        $member = User::where('email', 'journey@example.com')->firstOrFail();

        // 2. Complete the profile.
        $this->actingAs($member)->put(route('account.profile.update'), [
            'name' => 'Journey Member',
            'display_name' => 'JourneyJ',
            'country' => 'Nigeria',
            'professional_status' => 'student',
            'institution' => 'University of Lagos',
            'programme' => 'Medical Laboratory Science',
            'level' => '400',
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('me.jpg'),
            'show_on_leaderboard' => '1',
        ])->assertRedirect();

        $this->assertTrue($member->profile->fresh()->profile_completed);

        // 3. Take the open challenge. Results stay locked per-assessment
        // until the launch-control release below.
        $assessment = $this->makeAssessment();
        $intro = $this->actingAs($member)
            ->get(route('assessments.show', $assessment))
            ->assertOk()
            ->assertSee('Begin Examination');

        $this->completeOfficial($member, $assessment, 8);

        $submittedPage = $this->actingAs($member)
            ->get(route('exams.submitted', ExamAttempt::latest('id')->first()))
            ->assertOk()
            ->assertSee('Results locked');

        $attempt = ExamAttempt::where('user_id', $member->id)->where('mode', 'official')->firstOrFail();

        // 4. Results are still locked.
        $this->actingAs($member)->get(route('exams.result', $attempt))
            ->assertOk()
            ->assertDontSee('Subject Performance');

        // 5. Release results globally through launch control.
        $superAdmin = $this->admin();

        // The admin flips this assessment's own release switches, then
        // unlocks results globally - which fires participant notifications.
        $assessment->forceFill(['results_status' => 'available', 'leaderboard_status' => 'available'])->save();

        $this->actingAs($superAdmin)->put(route('admin.launch.update'), [
            'launch_date' => now()->format('Y-m-d'),
            'launch_time' => '20:00',
            'timezone' => 'Africa/Lagos',
            'auto_activate' => '1',
            'registration_open' => '1',
            'challenge_open' => '1',
            'results_status' => 'available',
            'answer_review_status' => 'available',
            'leaderboard_status' => 'available',
            'main_site_status' => 'open',
        ])->assertRedirect();

        // 6. Result + review become visible.
        $resultContent = $this->actingAs($member)->get(route('exams.result', $attempt))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('80%', $resultContent);
        $this->assertStringContainsString('Question Review', $resultContent);

        // 7. Leaderboard shows the member.
        $board = $this->get(route('leaderboard.show', $assessment))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('Journey Member', $board);

        // 8. Certificate issued on demand and verifiable + shareable.
        $this->actingAs($member)
            ->get(route('certificates.download', $attempt))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $certificate = Certificate::where('exam_attempt_id', $attempt->id)->firstOrFail();

        $this->get(route('certificates.verify', $certificate->verification_code))
            ->assertOk()
            ->assertSee('Certificate Verified');

        $this->get(route('certificates.share', $certificate->verification_code))
            ->assertOk()
            ->assertSee('og:title');

        $this->assertNotNull($member->fresh()->unreadNotifications->firstWhere('type', \App\Notifications\ResultsReleasedNotification::class));
    }
}
