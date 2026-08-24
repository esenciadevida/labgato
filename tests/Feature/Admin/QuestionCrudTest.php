<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->roles()->attach(
            Role::where('slug', 'super-administrator')->firstOrFail()
        );
    }

    private function validPayload(array $overrides = []): array
    {
        $category = $overrides['category'] ?? QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );

        return array_merge([
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'difficulty' => 'intermediate',
            'status' => 'draft',
            'stem' => 'Which parameter defines anemia in adult males?',
            'explanation' => 'Hemoglobin below 13 g/dL.',
            'options' => ['Low hemoglobin', 'High hemoglobin', 'Low platelets', 'High platelets'],
            'correct_index' => 0,
        ], $overrides);
    }

    private function makeQuestion(array $attributes = [], ?array $options = null): Question
    {
        $category = QuestionCategory::firstOrCreate(
            ['slug' => 'hematology'],
            ['name' => 'Hematology']
        );

        $question = Question::create(array_merge([
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'difficulty' => 'intermediate',
            'status' => 'active',
            'stem' => 'Sample stem for testing.',
        ], $attributes));

        foreach ($options ?? [['A', true], ['B', false]] as $index => [$label, $correct]) {
            $question->options()->create([
                'label' => $label,
                'text' => "Option {$label} text",
                'is_correct' => $correct,
                'sort_order' => $index,
            ]);
        }

        return $question;
    }

    public function test_index_lists_questions_and_applies_filters(): void
    {
        $hematology = QuestionCategory::create(['name' => 'Hematology', 'slug' => 'hematology']);
        $chemistry = QuestionCategory::create(['name' => 'Clinical Chemistry', 'slug' => 'clinical-chemistry']);

        $this->makeQuestion([
            'question_category_id' => $hematology->id,
            'stem' => 'Unique hemoglobin stem here',
            'difficulty' => 'easy',
            'status' => 'draft',
        ]);
        $this->makeQuestion([
            'question_category_id' => $chemistry->id,
            'stem' => 'Sodium puzzle stem',
            'difficulty' => 'advanced',
        ]);

        $this->actingAs($this->superAdmin)->get('/admin/questions')
            ->assertOk()
            ->assertSee('Unique hemoglobin stem here')
            ->assertSee('Sodium puzzle stem');

        $this->actingAs($this->superAdmin)->get('/admin/questions?search=hemoglobin')
            ->assertOk()
            ->assertSee('Unique hemoglobin stem here')
            ->assertDontSee('Sodium puzzle stem');

        $this->actingAs($this->superAdmin)->get("/admin/questions?category={$chemistry->id}")
            ->assertOk()
            ->assertSee('Sodium puzzle stem');

        $this->actingAs($this->superAdmin)->get('/admin/questions?status=draft')
            ->assertOk()
            ->assertSee('Unique hemoglobin stem here')
            ->assertDontSee('Sodium puzzle stem');
    }

    public function test_store_creates_question_with_options_and_correct_flag(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post('/admin/questions', $this->validPayload());

        $response->assertRedirect(route('admin.questions.index'));

        $question = Question::where('stem', 'like', 'Which parameter defines anemia%')->firstOrFail();

        $this->assertSame(4, $question->options()->count());
        $this->assertTrue($question->correctOption()->where('label', 'A')->exists());

        $labels = $question->options()->orderBy('sort_order')->pluck('is_correct', 'label');
        $this->assertTrue($labels['A']);
        $this->assertFalse($labels['B']);
        $this->assertFalse($labels['C']);
    }

    public function test_validation_rejects_bad_option_sets(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/questions', $this->validPayload(['correct_index' => 9]))
            ->assertSessionHasErrors('correct_index');

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions', $this->validPayload(['options' => ['Only one'], 'correct_index' => 0]))
            ->assertSessionHasErrors('options');

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions', $this->validPayload(['options' => ['', '', '', ''], 'correct_index' => 0]))
            ->assertSessionHasErrors();
    }

    public function test_image_upload_stores_file_and_replacement_deletes_old(): void
    {
        Storage::fake('public');

        $payload = $this->validPayload([
            'question_type' => 'image',
            'image' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $this->actingAs($this->superAdmin)->post('/admin/questions', $payload);

        $created = Question::where('stem', 'like', 'Which parameter defines anemia%')->firstOrFail();
        $oldPath = $created->image_path;

        $this->assertNotNull($oldPath);
        $this->assertStringStartsWith("questions/{$created->id}/", $oldPath);
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($this->superAdmin)
            ->put("/admin/questions/{$created->id}", $this->validPayload([
                'category' => QuestionCategory::find($created->question_category_id),
                'question_type' => 'image',
                'image' => UploadedFile::fake()->image('second.png'),
            ]));

        Storage::disk('public')->assertMissing($oldPath);
        $newPath = $created->fresh()->image_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_archive_and_restore_update_status(): void
    {
        $question = $this->makeQuestion();

        $this->actingAs($this->superAdmin)
            ->post("/admin/questions/{$question->id}/archive")
            ->assertRedirect();

        $this->assertSame('archived', $question->fresh()->status);

        $this->actingAs($this->superAdmin)
            ->post("/admin/questions/{$question->id}/restore")
            ->assertRedirect();

        $this->assertSame('active', $question->fresh()->status);
    }

    public function test_delete_is_blocked_for_questions_used_in_exams(): void
    {
        $question = $this->makeQuestion();

        $user = User::factory()->member()->create();
        $assessment = Assessment::create(['title' => 'T', 'slug' => 't-delete-guard']);
        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
        ]);

        ExamAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'question_id' => $question->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete("/admin/questions/{$question->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('questions', ['id' => $question->id]);

        $fresh = $question->fresh();
        $this->assertNotNull($fresh, 'Question must still exist after guarded delete.');
    }

    public function test_delete_removes_unused_question(): void
    {
        $question = $this->makeQuestion();

        $this->assertFalse($question->usedInExams());

        $this->actingAs($this->superAdmin)
            ->delete("/admin/questions/{$question->id}")
            ->assertRedirect(route('admin.questions.index'));

        $this->assertModelMissing($question);
        $this->assertSame(0, $question->options()->count());
    }

    public function test_bulk_archive_and_activate_work(): void
    {
        $one = $this->makeQuestion(['status' => 'draft'], [['A', true], ['B', false]]);
        $two = $this->makeQuestion(['status' => 'draft'], [['A', true], ['B', false]]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/bulk', ['action' => 'archive', 'ids' => [$one->id, $two->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('archived', $one->fresh()->status);
        $this->assertSame('archived', $two->fresh()->status);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/bulk', ['action' => 'activate', 'ids' => [$one->id, $two->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('active', $one->fresh()->status);
        $this->assertSame('active', $two->fresh()->status);
    }

    public function test_member_cannot_access_admin_questions(): void
    {
        $member = User::factory()->member()->create(['is_active' => true]);

        $this->actingAs($member)->get('/admin/questions')->assertStatus(403);
        $this->actingAs($member)->get('/admin/questions/create')->assertStatus(403);
    }
}
