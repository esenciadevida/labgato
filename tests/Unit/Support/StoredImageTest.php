<?php

namespace Tests\Unit\Support;

use App\Models\ContentImage;
use App\Support\StoredImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoredImageTest extends TestCase
{
    use RefreshDatabase;

    private const IMAGE_PATH = 'content/knowledge/2026/08/sample.webp';

    protected function setUp(): void
    {
        parent::setUp();

        StoredImage::flush();
    }

    private function seedImage(): void
    {
        ContentImage::create([
            'resource_type' => 'knowledge',
            'disk' => 'public',
            'path' => self::IMAGE_PATH,
            'original_name' => 'sample.webp',
            'mime_type' => 'image/webp',
            'size_bytes' => 100,
            'width' => 800,
            'height' => 600,
        ]);
    }

    public function test_resolve_reads_dimensions_from_content_images_row(): void
    {
        $this->seedImage();

        $dims = StoredImage::resolve('/storage/'.self::IMAGE_PATH);

        $this->assertSame(['width' => 800, 'height' => 600], $dims);
    }

    public function test_resolve_returns_null_for_external_images(): void
    {
        $this->assertNull(StoredImage::resolve('https://example.com/banner.webp'));
        $this->assertNull(StoredImage::resolve('data:image/png;base64,AAAA'));
        $this->assertNull(StoredImage::resolve(''));
    }

    public function test_variants_skips_missing_files(): void
    {
        $this->seedImage();

        $this->assertSame([], StoredImage::variants('/storage/'.self::IMAGE_PATH));
    }

    public function test_variants_includes_existing_responsive_files(): void
    {
        $this->seedImage();
        Storage::fake('public');
        Storage::disk('public')->put('content/knowledge/2026/08/sample-640w.webp', 'variants-bytes');

        $variants = StoredImage::variants('/storage/'.self::IMAGE_PATH);

        $this->assertSame([800, 640], array_column($variants, 'width'));
        $this->assertStringEndsWith('-640w.webp', $variants[1]['src']);
    }

    public function test_decorate_injects_dimensions_and_srcset(): void
    {
        $this->seedImage();
        Storage::fake('public');

        Storage::disk('public')->put('content/knowledge/2026/08/sample-640w.webp', 'bytes');

        $html = '<p><img src="/storage/'.self::IMAGE_PATH.'" alt="chart"></p>';

        $result = StoredImage::decorate($html);

        $this->assertStringContainsString('width="800"', $result);
        $this->assertStringContainsString('height="600"', $result);
        $this->assertStringContainsString('srcset=', $result);
        $this->assertStringContainsString('sizes=', $result);
        $this->assertStringContainsString('-640w.webp 640w', $result);
    }

    public function test_decorate_leaves_external_images_untouched(): void
    {
        $this->seedImage();

        $html = '<img src="https://example.com/banner.webp" alt="external">';

        $this->assertSame($html, StoredImage::decorate($html));
    }

    public function test_rich_text_render_decorates_content_images(): void
    {
        $this->seedImage();

        $markdown = "![Westgard chart](/storage/".self::IMAGE_PATH.")\n\nSome text.";

        $result = \App\Support\RichTextSanitizer::render($markdown);

        $this->assertStringContainsString('width="800"', $result);
        $this->assertStringContainsString('height="600"', $result);
        $this->assertStringContainsString('loading="lazy"', $result);
    }
}