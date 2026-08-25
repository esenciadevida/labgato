<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\QuestionCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentBlueprintTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->roles()->attach(Role::where('slug', 'super-administrator')->firstOrFail());
    }

    private function categories(): array
    {
        $heme = QuestionCategory::create(['name' => 'Hematology', 'slug' => 'hematology']);
        $micro = QuestionCategory::create(['name' => 'Microbiology', 'slug' => 'microbiology']);

        return [$heme, $micro];
    }

    private function validPayload(QuestionCategory $heme, QuestionCategory $micro): array
    {
        return [
            'title' => 'Regression Assessment',
            'slug' => 'regression-assessment',
            'type' => 'challenge',
            'status' => 'scheduled',
            'duration_minutes' => 60,
            'pass_percentage' => 70,
            'blueprints' => [
                ['question_category_id' => $heme->id, 'include' => 1, 'question_count' => 5, 'easy_percent' => 30, 'intermediate_percent' => 40, 'advanced_percent' => 30],
                ['question_category_id' => $micro->id, 'include' => 1, 'question_count' => 3, 'easy_percent' => 30, 'intermediate_percent' => 40, 'advanced_percent' => 30],
            ],
        ];
    }

    public function test_create_form_renders_without_error(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.assessments.create'))
            ->assertOk();
    }

    public function test_blueprint_counts_auto_sync_total_questions(): void
    {
        [$heme, $micro] = $this->categories();

        $this->actingAs($this->superAdmin)
            ->post(route('admin.assessments.store'), $this->validPayload($heme, $micro))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $assessment = Assessment::firstOrFail();

        $this->assertSame('Regression Assessment', $assessment->title);
        $this->assertSame(8, $assessment->total_questions);
        $this->assertSame(2, $assessment->blueprints()->count());
        $this->assertSame(5, $assessment->blueprints()->where('question_category_id', $heme->id)->value('question_count'));
    }

    public function test_included_blueprint_difficulty_must_sum_to_100(): void
    {
        [$heme, $micro] = $this->categories();

        $payload = $this->validPayload($heme, $micro);
        $payload['blueprints'][0]['easy_percent'] = 30;
        $payload['blueprints'][0]['intermediate_percent'] = 30;
        $payload['blueprints'][0]['advanced_percent'] = 30;

        $this->actingAs($this->superAdmin)
            ->post(route('admin.assessments.store'), $payload)
            ->assertSessionHasErrors('blueprints.0.easy_percent');
    }
}
