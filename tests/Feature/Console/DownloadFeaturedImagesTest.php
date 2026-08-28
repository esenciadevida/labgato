<?php

namespace Tests\Feature\Console;

use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadFeaturedImagesTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_BYTES = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    private function seedExternalImages(): void
    {
        KnowledgeResource::factory()->create(['featured_image' => 'https://example.com/knowledge.jpg']);
        Sop::factory()->create(['featured_image' => 'https://example.com/sop.jpg']);
        InterpretationResource::factory()->create(['featured_image' => 'https://example.com/interpretation.jpg']);
        Sop::factory()->create(['featured_image' => 'content/local.webp']);
    }

    public function test_external_featured_images_are_downloaded_and_self_hosted(): void
    {
        Storage::fake('public');
        Http::fake([
            'example.com/*' => Http::response(base64_decode(self::PNG_BYTES), 200, ['Content-Type' => 'image/png']),
        ]);
        $this->seedExternalImages();

        $this->artisan('featured-images:download')->assertSuccessful();

        foreach ([KnowledgeResource::class, Sop::class, InterpretationResource::class] as $model) {
            foreach ($model::where('featured_image', 'like', 'https://%')->get() as $resource) {
                $this->assertStringStartsWith('content/', (string) $resource->featured_image);
                $this->assertMatchesRegularExpression('/\.webp$/', (string) $resource->featured_image);
                Storage::disk('public')->assertExists($resource->featured_image);
            }
        }
    }

    public function test_local_featured_images_are_left_untouched(): void
    {
        Storage::fake('public');
        Http::fake();

        $local = Sop::factory()->create(['featured_image' => 'content/local.webp']);

        $this->artisan('featured-images:download')->assertSuccessful();

        $this->assertSame('content/local.webp', $local->refresh()->featured_image);
    }

    public function test_dry_run_does_not_modify_records(): void
    {
        Http::fake();
        $external = KnowledgeResource::factory()->create(['featured_image' => 'https://example.com/knowledge.jpg']);

        $this->artisan('featured-images:download', ['--dry-run' => true])
            ->expectsOutputToContain('Would download')
            ->assertSuccessful();

        $this->assertSame('https://example.com/knowledge.jpg', $external->refresh()->featured_image);
    }

    public function test_failed_download_keeps_existing_featured_image(): void
    {
        Http::fake([
            '*/*' => Http::response('', 404, []),
        ]);
        Sop::factory()->create(['featured_image' => 'content/local.webp']);
        $external = Sop::factory()->create(['featured_image' => 'https://example.com/unreachable.jpg']);

        $this->artisan('featured-images:download')->assertSuccessful();

        $this->assertSame('https://example.com/unreachable.jpg', $external->refresh()->featured_image);
    }
}