<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\Permission;
use App\Models\QuestionCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentFormRenderTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['is_active' => true, 'user_type' => 'admin']);
        $role = Role::factory()->create();

        foreach (['assessments.manage', 'dashboard.view'] as $slug) {
            $role->permissions()->attach(Permission::factory()->create(['slug' => $slug]));
        }

        $user->roles()->attach($role);

        return $user;
    }

    public function test_create_page_renders_without_error(): void
    {
        $response = $this->actingAs($this->adminUser())->get(route('admin.assessments.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Assessment');
        $response->assertDontSee('x-data="{ rows: [', false);
    }

    public function test_edit_page_renders_without_error_and_escapes_blueprint_json(): void
    {
        $category = QuestionCategory::create([
            'name' => 'Hematology',
            'slug' => 'hematology',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $assessment = Assessment::create([
            'title' => 'ASCPI Challenge 2026',
            'slug' => 'ascpi-challenge-2026',
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 150,
            'total_questions' => 10,
            'max_attempts' => 1,
            'allow_practice' => true,
            'certificate_enabled' => true,
            'pass_percentage' => 70,
            'results_status' => 'available',
            'answer_review_status' => 'available',
            'leaderboard_status' => 'available',
        ]);

        AssessmentBlueprint::create([
            'assessment_id' => $assessment->id,
            'question_category_id' => $category->id,
            'question_count' => 10,
            'easy_percent' => 30,
            'intermediate_percent' => 50,
            'advanced_percent' => 20,
        ]);

        $response = $this->actingAs($this->adminUser())->get(route('admin.assessments.edit', $assessment));

        $response->assertStatus(200);
        $response->assertSee($assessment->title);
        $response->assertDontSee('x-data="{ rows: [', false);
        $response->assertSee('data-rows="', false);
    }
}
