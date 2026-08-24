<?php

namespace Tests\Feature\Admin;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionImportTest extends TestCase
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

    private function uploadFromString(string $content, string $filename = 'questions.csv'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $content);

        return new UploadedFile($path, $filename, 'text/csv', null, true);
    }

    private function csvWithCategories(): array
    {
        return [
            QuestionCategory::create(['name' => 'Hematology', 'slug' => 'hematology']),
            QuestionCategory::create(['name' => 'Clinical Chemistry', 'slug' => 'clinical-chemistry']),
        ];
    }

    public function test_template_downloads_with_header_and_examples(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/admin/questions/import/template');

        $response->assertOk()
            ->assertHeaderContains('Content-Type', 'text/csv')
            ->assertDownload('questions_import_template.csv');

        $content = $response->getContent();

        $this->assertStringContainsString('category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation', $content);
        $this->assertStringContainsString('Hematology', $content);
        $this->assertStringContainsString('Clinical Chemistry', $content);
    }

    public function test_preview_flags_invalid_rows_with_reasons(): void
    {
        $this->csvWithCategories();

        $csv = implode("\n", [
            'category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation',
            'Nonexistent Subject,text,easy,Valid stem here,Alpha,Beta,,D,A,',
            'Hematology,text,easy,,Alpha,Beta,,D,B,missing stem',
            'Clinical Chemistry,text,weird,Stem text,Alpha,Beta,,D,Z,bad letter',
            'Clinical Chemistry,text,advanced,Good stem,Alpha,Beta,,,B,',
        ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/import/preview', ['file' => $this->uploadFromString($csv)])
            ->assertOk()
            ->assertSee('1 valid')
            ->assertSee('3 invalid')
            ->assertSee('Unknown subject')
            ->assertSee('Stem is required')
            ->assertSee('Invalid correct option');
    }

    public function test_import_creates_only_valid_rows_as_draft_by_default(): void
    {
        [$hematology] = $this->csvWithCategories();

        $csv = implode("\n", [
            'category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation',
            'Hematology,text,easy,Retic count question,One,Two,,Four,D,some rationale',
            'Bad Subject,text,easy,Should not import,One,Two,,D,A,',
        ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/import/preview', ['file' => $this->uploadFromString($csv)]);

        $this->actingAs($this->superAdmin)->post('/admin/questions/import');

        $this->assertSame(1, Question::count());
        $question = Question::firstOrFail();

        $this->assertEquals($hematology->id, $question->question_category_id);
        $this->assertSame('draft', $question->status);

        $options = $question->options()->orderBy('sort_order')->get();
        $this->assertCount(3, $options);
        $this->assertSame('C', $options->firstWhere('is_correct', true)?->label);
    }

    public function test_import_as_active_sets_active_status(): void
    {
        $this->csvWithCategories();

        $csv = implode("\n", [
            'category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation',
            'Clinical Chemistry,calculation,intermediate,Gap question,Low,High,,,A,',
        ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/import/preview', [
                'file' => $this->uploadFromString($csv),
                'import_as' => 'active',
            ]);

        $this->actingAs($this->superAdmin)->post('/admin/questions/import');

        $this->assertSame('active', Question::firstOrFail()->status);
    }

    public function test_correct_letter_maps_to_the_right_index_when_options_are_sparse(): void
    {
        $this->csvWithCategories();

        $csv = implode("\n", [
            'category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation',
            'Hematology,text,easy,Sparse options question,First,,Third,,C,',
        ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/import/preview', ['file' => $this->uploadFromString($csv)]);

        $this->actingAs($this->superAdmin)->post('/admin/questions/import');

        $options = QuestionOption::orderBy('sort_order')->get();
        $this->assertCount(2, $options);
        $this->assertSame(['First', 'Third'], $options->pluck('text')->all());
        $this->assertTrue((bool) $options[1]->is_correct);
        $this->assertFalse((bool) $options[0]->is_correct);
    }

    /* ------------------------------------------------------- image columns */

    public function test_template_includes_optional_image_columns(): void
    {
        $content = $this->actingAs($this->superAdmin)
            ->get('/admin/questions/import/template')
            ->getContent();

        $this->assertStringContainsString(',image_url,image_alt', $content);
    }

    public function test_image_row_is_downloaded_and_attached_as_text_image(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/*' => Http::response(base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
            ), 200, ['Content-Type' => 'image/png']),
        ]);

        $this->csvWithCategories();

        $header = 'category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation,image_url,image_alt';
        $csv = implode("\n", [
            $header,
            'Hematology,text,easy,Blood film question,One,Two,,D,A,,https://example.com/film.png,A metamyelocyte',
        ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/import/preview', ['file' => $this->uploadFromString($csv)])
            ->assertSee('1 valid');

        $this->actingAs($this->superAdmin)->post('/admin/questions/import');

        $question = Question::firstOrFail();
        $this->assertSame('text_image', $question->question_type);
        $this->assertNotNull($question->image_path);
        $this->assertSame('A metamyelocyte', $question->image_alt);
        Storage::disk('public')->assertExists($question->image_path);
    }

    public function test_invalid_local_image_reference_flags_the_row(): void
    {
        Storage::fake('public');
        $this->csvWithCategories();

        $header = 'category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation,image_url,image_alt';
        $csv = implode("\n", [
            $header,
            'Hematology,text,easy,Bad image row,One,Two,,D,A,,questions/missing.png,',
        ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/import/preview', ['file' => $this->uploadFromString($csv)])
            ->assertOk()
            ->assertSee('neither a valid http(s) URL');
    }

    public function test_calculation_rows_reject_images(): void
    {
        $this->csvWithCategories();

        $header = 'category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation,image_url,image_alt';
        $csv = implode("\n", [
            $header,
            'Clinical Chemistry,calculation,easy,Gap math with image,Low,High,,,A,,https://example.com/x.png,',
        ]);

        $this->actingAs($this->superAdmin)
            ->post('/admin/questions/import/preview', ['file' => $this->uploadFromString($csv)])
            ->assertOk()
            ->assertSee('cannot be combined with image_url');
    }
}
