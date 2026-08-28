<?php

namespace Tests\Feature\Console;

use App\Enums\ResourceStatus;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MasterContentImportTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        File::delete($this->tempFixture());

        parent::tearDown();
    }

    private function tempFixture(): string
    {
        return sys_get_temp_dir().'/labgoto_import_test.json';
    }

    private function writeFixture(array $records): string
    {
        File::put($this->tempFixture(), json_encode($records, JSON_UNESCAPED_SLASHES));

        return $this->tempFixture();
    }

    public function test_imports_sops_from_dump_file(): void
    {
        $this->artisan('master-content:import', [
            'file' => 'dump/labgoto_hematology_sops.json',
            '--type' => 'sops',
        ])->assertExitCode(0);

        $this->assertSame(18, Sop::query()->count());

        $sop = Sop::query()->where('slug', 'manual-complete-blood-count-cbc-fbc')->first();

        $this->assertNotNull($sop);
        $this->assertSame(ResourceStatus::PUBLISHED, $sop->status);
        $this->assertNotNull($sop->published_at);
        $this->assertNull($sop->featured_image);
    }

    public function test_sop_import_is_idempotent(): void
    {
        $this->artisan('master-content:import', [
            'file' => 'dump/labgoto_hematology_sops.json',
            '--type' => 'sops',
        ])->expectsOutputToContain('18 created');

        $this->artisan('master-content:import', [
            'file' => 'dump/labgoto_hematology_sops.json',
            '--type' => 'sops',
        ])->expectsOutputToContain('18 updated');

        $this->assertSame(18, Sop::query()->count());
    }

    public function test_imports_knowledge_with_snake_case_fields_and_image_url(): void
    {
        $records = [
            [
                'name' => 'Colony Counting and Reporting',
                'slug' => 'colony-counting-and-reporting',
                'short_description' => 'How to count and report bacterial colonies.',
                'content' => '# Colony Counting',
                'topic_category' => 'Microbiology',
                'difficulty_level' => 'intermediate',
                'author_name' => 'LabGOTO Editorial Team',
                'image_url' => 'https://media.licdn.com/dms/image/example.jpg',
                'reading_time_minutes' => 6,
                'status' => 'published',
                'categories' => ['Microbiology'],
                'tags' => ['Colony Count', 'cfu'],
            ],
        ];

        $this->artisan('master-content:import', [
            'file' => $this->writeFixture($records),
            '--type' => 'knowledge',
        ])->assertExitCode(0);

        $knowledge = KnowledgeResource::query()->where('slug', 'colony-counting-and-reporting')->first();

        $this->assertNotNull($knowledge);
        $this->assertSame('https://media.licdn.com/dms/image/example.jpg', $knowledge->featured_image);
        $this->assertSame('Intermediate', $knowledge->difficulty_level);
        $this->assertSame(ResourceStatus::PUBLISHED, $knowledge->status);
        $this->assertTrue($knowledge->tags->contains(fn ($tag) => $tag->name === 'cfu'));
    }

    public function test_import_skips_unresolved_featured_image_placeholders(): void
    {
        $records = [
            [
                'name' => 'Unresolved Image SOP',
                'slug' => 'unresolved-image-sop',
                'content' => '# Test',
                'status' => 'draft',
                'featured_image' => 'IMAGE_URL_TO_BE_CONFIRMED',
            ],
        ];

        $this->artisan('master-content:import', [
            'file' => $this->writeFixture($records),
            '--type' => 'sops',
        ])->assertExitCode(0);

        $this->assertNull(Sop::query()->where('slug', 'unresolved-image-sop')->first()->featured_image);
    }

    public function test_rejects_unknown_type(): void
    {
        $this->artisan('master-content:import', [
            'file' => 'dump/labgoto_hematology_sops.json',
            '--type' => 'calculators',
        ])->assertExitCode(1);
    }

    public function test_rejects_missing_file(): void
    {
        $this->artisan('master-content:import', [
            'file' => 'dump/nonexistent.json',
            '--type' => 'sops',
        ])->assertExitCode(1);
    }
}