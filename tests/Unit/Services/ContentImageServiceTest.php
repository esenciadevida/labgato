<?php

namespace Tests\Unit\Services;

use App\Services\ContentImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentImageServiceTest extends TestCase
{
    public function test_square_crop_centers_and_squares_a_wide_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.png', 800, 600);

        $result = app(ContentImageService::class)->process($file, 'profiles', true);

        $this->assertSame($result['width'], $result['height']);
        $this->assertLessThanOrEqual(512, $result['width']);
        $this->assertGreaterThan(0, $result['width']);
        $this->assertStringEndsWith('.webp', $result['path']);
        Storage::disk('public')->assertExists($result['path']);
    }

    public function test_square_crop_never_upscales_a_small_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.png', 120, 90);

        $result = app(ContentImageService::class)->process($file, 'profiles', true);

        $this->assertSame($result['width'], $result['height']);
        $this->assertSame(90, $result['width']);
    }

    public function test_non_square_preserves_aspect_ratio(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.png', 800, 600);

        $result = app(ContentImageService::class)->process($file, 'content');

        $this->assertSame(800, $result['width']);
        $this->assertSame(600, $result['height']);
    }

    public function test_content_image_writes_responsive_variants(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('chart.png', 1200, 800);

        $result = app(ContentImageService::class)->process($file, 'content');

        $disk = Storage::disk('public');
        $name = pathinfo($result['path'], PATHINFO_FILENAME);
        $directory = pathinfo($result['path'], PATHINFO_DIRNAME);

        $disk->assertExists($result['path']);
        $this->assertSame([320, 640, 960], $result['variants']);

        $disk->assertExists("{$directory}/{$name}-320w.webp");
        $disk->assertExists("{$directory}/{$name}-640w.webp");
        $disk->assertExists("{$directory}/{$name}-960w.webp");
        $this->assertFalse($disk->exists("{$directory}/{$name}-1280w.webp"));
    }

    public function test_delete_with_variants_removes_base_and_variants(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('chart.png', 1200, 800);

        $result = app(ContentImageService::class)->process($file, 'content');

        $disk = Storage::disk('public');
        $name = pathinfo($result['path'], PATHINFO_FILENAME);
        $directory = pathinfo($result['path'], PATHINFO_DIRNAME);

        app(ContentImageService::class)->deleteWithVariants($result['path']);

        $this->assertFalse($disk->exists($result['path']));
        $this->assertFalse($disk->exists("{$directory}/{$name}-320w.webp"));
        $this->assertFalse($disk->exists("{$directory}/{$name}-640w.webp"));
    }

    public function test_generate_variants_for_path_backfills_missing_variants(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('legacy.png', 1200, 800);
        $result = app(ContentImageService::class)->process($file, 'content');

        $disk = Storage::disk('public');
        $name = pathinfo($result['path'], PATHINFO_FILENAME);
        $directory = pathinfo($result['path'], PATHINFO_DIRNAME);

        foreach ([320, 640, 960] as $width) {
            $disk->delete("{$directory}/{$name}-{$width}w.webp");
        }

        $written = app(ContentImageService::class)->generateVariantsForPath($result['path']);

        $this->assertSame([320, 640, 960], $written);
        $disk->assertExists("{$directory}/{$name}-640w.webp");

        $rerun = app(ContentImageService::class)->generateVariantsForPath($result['path']);
        $this->assertSame([], $rerun);
    }
}
