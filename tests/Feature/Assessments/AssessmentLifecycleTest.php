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
