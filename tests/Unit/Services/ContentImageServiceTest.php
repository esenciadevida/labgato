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
}
