<?php

namespace Tests\Feature\Questions;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class JsonSeedTest extends TestCase
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

    private function fixture(string $content): string
    {
        $path = storage_path('app/json-test/fixture.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        return 'storage/app/json-test/fixture.json';
    }

    private function seedFixture(array $items): string
    {
        return $this->fixture(json_encode($items));
    }

    public function test_seeder_creates_questions_options_and_metadata(): void
    {
        $rel = $this->seedFixture([
            [
                'category' => 'Hematology',
                'type' => 'image',
                'difficulty' => 'intermediate',
                'stem' => 'The smear shows target cells. What morphology is shown?',
                'image' => 'images/questions/h-tc.jpg',
                'image_source' => 'Wikimedia Commons',
                'image_license' => 'CC BY-SA 4.0',
                'image_attribution' => 'By User:X, CC BY-SA 4.0',
                'image_url' => 'https://example.com/target.jpg',
                'options' => ['Spherocytes', 'Target cells', 'Schistocytes', 'Stomatocytes'],
                'correct' => 'B',
                'explanation' => 'Bull\'s-eye morphology.',
                'status' => 'draft',
            ],
            [
                'category' => 'Hematology',
                'type' => 'text',
                'difficulty' => 'easy',
                'stem' => 'Adult male Hb reference range?',
                'options' => ['14-18', '12-16', '11-14', '15-20'],
                'correct' => 'A',
                'status' => 'draft',
            ],
        ]);

        $this->artisan('questions:seed', ['file' => $rel])->assertSuccessful();

        $this->assertSame(2, Question::count());

        $question = Question::where('stem', 'like', 'The smear shows target cells%')->firstOrFail();
        $this->assertSame('Wikimedia Commons', $question->image_source);
        $this->assertSame('CC BY-SA 4.0', $question->image_license);
        $this->assertSame('By User:X, CC BY-SA 4.0', $question->image_attribution);
        $this->assertSame('https://example.com/target.jpg', $question->image_source_url);
        $this->assertTrue($question->requiresAttribution());
        $this->assertSame('Target cells', $question->options()->where('is_correct', true)->first()->text);
        $this->assertSame('draft', $question->status);

        // Duplicate categories are reused (firstOrCreate).
        $this->assertSame(1, QuestionCategory::count());
    }

    public function test_invalid_json_structure_throws(): void
    {
        $rel = $this->fixture('{ not valid json');

        $this->expectException(\RuntimeException::class);

        $this->artisan('questions:seed', ['file' => $rel]);
    }

    public function test_duplicate_category_is_not_duplicated(): void
    {
        QuestionCategory::create(['name' => 'Hematology', 'slug' => 'hematology']);

        $rel = $this->seedFixture([
            ['category' => 'Hematology', 'type' => 'text', 'difficulty' => 'easy', 'stem' => 'S', 'options' => ['a', 'b'], 'correct' => 'A', 'status' => 'draft'],
        ]);

        $this->artisan('questions:seed', ['file' => $rel])->assertSuccessful();

        $this->assertSame(1, QuestionCategory::count());
    }

    public function test_attributions_page_lists_cc_licensed_questions(): void
    {
        $category = QuestionCategory::create(['name' => 'Hematology', 'slug' => 'hematology']);

        Question::create([
            'question_category_id' => $category->id,
            'question_type' => 'image',
            'stem' => 'CC licensed question',
            'image_license' => 'CC BY-SA 4.0',
            'image_source' => 'Wikimedia Commons',
            'image_attribution' => 'By User:X',
            'image_source_url' => 'https://example.com/a.jpg',
        ]);

        Question::create([
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'stem' => 'Public domain question',
            'image_license' => 'Public Domain',
        ]);

        $this->get(route('attributions.index'))
            ->assertOk()
            ->assertSee('CC licensed question')
            ->assertDontSee('Public domain question');
    }

    public function test_admin_form_saves_image_metadata(): void
    {
        $category = QuestionCategory::create(['name' => 'Hematology', 'slug' => 'hematology']);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.questions.store'), [
            'question_category_id' => $category->id,
            'question_type' => 'text',
            'difficulty' => 'easy',
            'status' => 'draft',
            'stem' => 'A question with image metadata.',
            'options' => ['Option A', 'Option B', 'Option C', 'Option D'],
            'correct_index' => 0,
            'image_source' => 'Wikimedia Commons',
            'image_license' => 'CC BY-SA 4.0',
            'image_attribution' => 'By User:X, CC BY-SA 4.0',
            'image_source_url' => 'https://example.com/img.jpg',
        ]);

        $response->assertRedirect();

        $question = Question::firstOrFail();
        $this->assertSame('Wikimedia Commons', $question->image_source);
        $this->assertSame('CC BY-SA 4.0', $question->image_license);
        $this->assertSame('By User:X, CC BY-SA 4.0', $question->image_attribution);
        $this->assertSame('https://example.com/img.jpg', $question->image_source_url);
        $this->assertTrue($question->requiresAttribution());
    }
}
