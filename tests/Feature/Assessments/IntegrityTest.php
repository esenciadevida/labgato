<?php

namespace Tests\Feature\Assessments;

use App\Models\ExamAttempt;
use App\Models\IntegrityEvent;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $superAdmin;

    protected ExamAttempt $attempt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->roles()->attach(
            Role::where('slug', 'super-administrator')->firstOrFail()
        );

        $this->user = User::factory()->member()->create();

        $category = QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );
        $question = Question::create([
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'stem' => 'Sample stem',
            'difficulty' => 'easy',
        ]);

        $assessment = \App\Models\Assessment::create([
            'title' => 'Integrity Test Challenge',
            'slug' => 'integrity-test-challenge',
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 90,
            'total_questions' => 1,
        ]);

        $this->attempt = ExamAttempt::create([
            'user_id' => $this->user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 1,
            'mode' => 'official',
            'status' => 'in_progress',
            'question_ids' => [$question->id],
            'option_orders' => [],
            'session_fingerprint' => sha1('127.0.0.1|Symfony'),
            'started_at' => now(),
            'ends_at' => now()->addMinutes(90),
            'integrity_status' => 'normal',
        ]);
    }

    public function test_endpoint_logs_a_batch_of_events_and_returns_204(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/exam/{$this->attempt->id}/integrity", [
                'events' => [
                    ['type' => 'tab_switch', 'q' => 3],
                    ['type' => 'copy_attempt', 'q' => 4, 'meta' => ['length' => 120]],
                ],
            ]);

        $response->assertStatus(204);

        $this->assertSame(2, IntegrityEvent::where('exam_attempt_id', $this->attempt->id)->count());

        $copy = IntegrityEvent::where('event_type', 'copy_attempt')->firstOrFail();
        $this->assertSame(4, $copy->question_number);
        $this->assertSame(2, $copy->weight);
        $this->assertSame(['length' => 120], $copy->metadata);
    }

    public function test_invalid_event_type_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->postJson("/exam/{$this->attempt->id}/integrity", [
                'events' => [['type' => 'hax', 'q' => 1]],
            ])
            ->assertStatus(422);

        $this->assertSame(0, IntegrityEvent::count());
    }

    public function test_weights_drive_normal_review_and_high_risk_thresholds(): void
    {
        // Two tab switches = weight 2 → still normal.
        $this->actingAs($this->user)->postJson("/exam/{$this->attempt->id}/integrity", [
            'events' => [
                ['type' => 'tab_switch'],
                ['type' => 'tab_switch'],
            ],
        ])->assertStatus(204);

        $this->assertSame('normal', $this->attempt->fresh()->integrity_status);

        // Two more tab switches (total 4) → review.
        $this->actingAs($this->user)->postJson("/exam/{$this->attempt->id}/integrity", [
            'events' => [
                ['type' => 'tab_switch'],
                ['type' => 'tab_switch'],
            ],
        ])->assertStatus(204);

        $this->assertSame('review', $this->attempt->fresh()->integrity_status);

        // Four copy attempts (total 12) → high_risk.
        $this->actingAs($this->user)->postJson("/exam/{$this->attempt->id}/integrity", [
            'events' => [
                ['type' => 'copy_attempt'],
                ['type' => 'copy_attempt'],
                ['type' => 'copy_attempt'],
                ['type' => 'copy_attempt'],
            ],
        ])->assertStatus(204);

        $this->assertSame('high_risk', $this->attempt->fresh()->integrity_status);
    }

    public function test_multiple_session_forces_high_risk(): void
    {
        $this->actingAs($this->user)->postJson("/exam/{$this->attempt->id}/integrity", [
            'events' => [['type' => 'multiple_session']],
        ])->assertStatus(204);

        $this->assertSame('high_risk', $this->attempt->fresh()->integrity_status);
    }

    public function test_session_fingerprint_mismatch_logs_session_change_once(): void
    {
        // The default fingerprint is sha1('127.0.0.1|Symfony'); test client matches it.
        $this->get(route('exams.show', $this->attempt));
        $this->assertSame(0, IntegrityEvent::where('event_type', 'session_change')->count());

        // A different fingerprint logs session_change exactly once, even across calls.
        $this->withHeader('User-Agent', 'Different-Agent/1.0')
            ->actingAs($this->user)
            ->get(route('exams.show', $this->attempt))
            ->assertOk();

        $this->actingAs($this->user)
            ->postJson(route('exams.flag', $this->attempt), [
                'question_id' => $this->attempt->question_ids[0],
            ])->assertOk();

        $this->assertSame(
            1,
            IntegrityEvent::where('exam_attempt_id', $this->attempt->id)
                ->where('event_type', 'session_change')
                ->count()
        );
    }

    public function test_cannot_post_events_to_another_users_attempt(): void
    {
        $intruder = User::factory()->member()->create();

        $this->actingAs($intruder)
            ->postJson("/exam/{$this->attempt->id}/integrity", [
                'events' => [['type' => 'tab_switch']],
            ])
            ->assertStatus(403);

        $this->assertSame(0, IntegrityEvent::count());
    }

    public function test_admin_index_filters_by_status_and_show_lists_events(): void
    {
        app(\App\Services\IntegrityService::class)->log($this->attempt, 'tab_switch', 2);
        app(\App\Services\IntegrityService::class)->log($this->attempt, 'copy_attempt', 2);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.integrity.index'))
            ->assertOk()
            ->assertSee($this->user->name);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.integrity.index', ['status' => 'high_risk']))
            ->assertOk()
            ->assertDontSee($this->user->name);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.integrity.show', $this->attempt))
            ->assertOk()
            ->assertSee('tab switch', false)
            ->assertSee('copy attempt', false)
            ->assertSee('Q2');
    }

    public function test_manual_override_updates_status(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.integrity.status', $this->attempt), [
                'status' => 'high_risk',
            ])
            ->assertRedirect(route('admin.integrity.show', $this->attempt));

        $this->assertSame('high_risk', $this->attempt->fresh()->integrity_status);
    }

    public function test_integrity_routes_blocked_for_non_admins(): void
    {
        $this->actingAs($this->user)->get(route('admin.integrity.index'))->assertStatus(403);
        $this->actingAs($this->user)->get(route('admin.integrity.show', $this->attempt))->assertStatus(403);
        $this->actingAs($this->user)->post(route('admin.integrity.status', $this->attempt), [
            'status' => 'normal',
        ])->assertStatus(403);
    }
}
