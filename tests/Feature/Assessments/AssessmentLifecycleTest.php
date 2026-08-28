<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\ExamAttempt;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(
            \App\Models\Role::where('slug', 'super-administrator')->firstOrFail()
        );

        return $admin;
    }

    private function makeAssessment(array $overrides = []): Assessment
    {
        $assessment = Assessment::create(array_merge([
            'title' => 'Monthly ASCPi Challenge',
            'slug' => 'monthly-ascpi-challenge',
            'description' => 'The flagship monthly assessment.',
            'type' => 'challenge',
            'status' => 'draft',
            'duration_minutes' => 60,
            'total_questions' => 10,
            'max_attempts' => 1,
            'allow_practice' => true,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'pass_percentage' => 70,
            'certificate_enabled' => true,
        ], $overrides));

        AssessmentBlueprint::create([
            'assessment_id' => $assessment->id,
            'question_category_id' => \App\Models\QuestionCategory::firstOrCreate(
                ['slug' => 'hematology'],
                ['name' => 'Hematology']
            )->id,
            'question_count' => 10,
            'easy_percent' => 50,
            'intermediate_percent' => 50,
            'advanced_percent' => 0,
        ]);

        return $assessment;
    }

    public function test_duplicate_creates_draft_copy_with_blueprints(): void
    {
        $assessment = $this->makeAssessment();
        $this->admin();

        $this->actingAs($this->admin())
            ->post(route('admin.assessments.duplicate', $assessment))
            ->assertRedirect();

        $copy = Assessment::where('slug', 'monthly-ascpi-challenge-copy')->firstOrFail();

        $this->assertSame('Monthly ASCPi Challenge (Copy)', $copy->title);
        $this->assertSame('draft', $copy->status);
        $this->assertNotSame($assessment->id, $copy->id);
        $this->assertSame(1, $copy->blueprints()->count());
        $this->assertSame(
            $assessment->blueprints()->first()->question_count,
            $copy->blueprints()->first()->question_count
        );
    }

    public function test_store_persists_certificate_customization_fields(): void
    {
        $category = \App\Models\QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );

        $this->actingAs($this->admin())
            ->post(route('admin.assessments.store'), [
                'title' => 'Custom Cert Challenge',
                'slug' => 'custom-cert-challenge',
                'type' => 'challenge',
                'status' => 'draft',
                'duration_minutes' => 60,
                'total_questions' => 5,
                'pass_percentage' => 70,
                'certificate_enabled' => '1',
                'cert_body_text' => 'for completing {assessment}',
                'cert_disclaimer_text' => 'Internal training only.',
                'cert_primary_color' => '#ff0000',
                'cert_secondary_color' => '#00ff00',
                'cert_signatory_name' => 'Dr. Person',
                'cert_signatory_title' => 'Course Lead',
                'cert_show_logo' => '0',
                'cert_show_qr' => '1',
                'blueprints' => [
                    [
                        'question_category_id' => $category->id,
                        'include' => '1',
                        'question_count' => 5,
                        'easy_percent' => 100,
                        'intermediate_percent' => 0,
                        'advanced_percent' => 0,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.assessments.index'));

        $this->assertDatabaseHas('assessments', [
            'title' => 'Custom Cert Challenge',
            'cert_body_text' => 'for completing {assessment}',
            'cert_disclaimer_text' => 'Internal training only.',
            'cert_primary_color' => '#ff0000',
            'cert_secondary_color' => '#00ff00',
            'cert_signatory_name' => 'Dr. Person',
            'cert_signatory_title' => 'Course Lead',
            'cert_show_logo' => false,
            'cert_show_qr' => true,
        ]);
    }

    public function test_store_blank_certificate_fields_inherit_globals(): void
    {
        $category = \App\Models\QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );

        $this->actingAs($this->admin())
            ->post(route('admin.assessments.store'), [
                'title' => 'Default Cert Challenge',
                'slug' => 'default-cert-challenge',
                'type' => 'challenge',
                'status' => 'draft',
                'duration_minutes' => 60,
                'total_questions' => 5,
                'pass_percentage' => 70,
                'certificate_enabled' => '1',
                'cert_body_text' => '',
                'cert_disclaimer_text' => '',
                'cert_primary_color' => '',
                'cert_secondary_color' => '',
                'cert_signatory_name' => '',
                'cert_signatory_title' => '',
                'cert_show_logo' => '',
                'cert_show_qr' => '',
                'blueprints' => [
                    [
                        'question_category_id' => $category->id,
                        'include' => '1',
                        'question_count' => 5,
                        'easy_percent' => 100,
                        'intermediate_percent' => 0,
                        'advanced_percent' => 0,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.assessments.index'));

        $this->assertDatabaseHas('assessments', [
            'title' => 'Default Cert Challenge',
            'cert_body_text' => null,
            'cert_disclaimer_text' => null,
            'cert_primary_color' => null,
            'cert_secondary_color' => null,
            'cert_signatory_name' => null,
            'cert_signatory_title' => null,
            'cert_show_logo' => null,
            'cert_show_qr' => null,
        ]);
    }

    public function test_store_rejects_invalid_certificate_color(): void
    {
        $category = \App\Models\QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );

        $this->actingAs($this->admin())
            ->post(route('admin.assessments.store'), [
                'title' => 'Bad Color Challenge',
                'slug' => 'bad-color-challenge',
                'type' => 'challenge',
                'status' => 'draft',
                'duration_minutes' => 60,
                'total_questions' => 5,
                'pass_percentage' => 70,
                'certificate_enabled' => '1',
                'cert_primary_color' => 'red',
                'blueprints' => [
                    [
                        'question_category_id' => $category->id,
                        'include' => '1',
                        'question_count' => 5,
                        'easy_percent' => 100,
                        'intermediate_percent' => 0,
                        'advanced_percent' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors('cert_primary_color');

        $this->assertDatabaseMissing('assessments', ['slug' => 'bad-color-challenge']);
    }

    public function test_hub_page_renders_configurable_copy(): void
    {
        $this->makeAssessment(['status' => 'open']);
        $member = User::factory()->create(['is_active' => true, 'user_type' => 'member']);

        $response = $this->actingAs($member)->get(route('assessments.index'));

        $response->assertOk();
        $response->assertSee(setting('hub.eyebrow', 'ASCPi Challenge'));
        $response->assertSee(setting('hub.heading', 'Assessments'));
        $response->assertSee(setting('hub.card_cta', 'Take Challenge'));
        $response->assertSee(setting('hub.how_works_title', 'How it works'));
        $response->assertSee(setting('hub.practice_title', 'Practice makes perfect'));
    }

    public function test_destroy_blocks_when_attempts_exist(): void
    {
        $assessment = $this->makeAssessment(['status' => 'closed']);
        $admin = $this->admin();

        ExamAttempt::create([
            'user_id' => User::factory()->create()->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 1,
            'mode' => 'official',
            'status' => 'submitted',
            'question_ids' => [],
            'option_orders' => [],
            'started_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.assessments.destroy', $assessment))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('assessments', ['id' => $assessment->id]);
    }

    public function test_destroy_blocks_while_open(): void
    {
        $assessment = $this->makeAssessment(['status' => 'open']);
        $this->admin();

        $this->actingAs($this->admin())
            ->delete(route('admin.assessments.destroy', $assessment))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('assessments', ['id' => $assessment->id]);
    }

    public function test_destroy_removes_closed_assessment_without_attempts(): void
    {
        $assessment = $this->makeAssessment(['status' => 'closed']);
        $this->admin();

        $this->actingAs($this->admin())
            ->delete(route('admin.assessments.destroy', $assessment))
            ->assertRedirect(route('admin.assessments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]);
        $this->assertDatabaseMissing('assessment_blueprints', ['assessment_id' => $assessment->id]);
    }
}
