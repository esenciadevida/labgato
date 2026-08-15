<?php

namespace Tests\Feature\Seo;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\ReferenceRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapContentTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(public_path('sitemap.xml'));
        parent::tearDown();
    }

    public function test_sitemap_includes_all_published_resource_types(): void
    {
        $calculator = Calculator::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $range = ReferenceRange::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $interpretation = InterpretationResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);
        $knowledge = KnowledgeResource::factory()->create(['status' => ResourceStatus::PUBLISHED]);

        $this->artisan('sitemap:generate');

        $xml = simplexml_load_file(public_path('sitemap.xml'));
        $locs = [];
        foreach ($xml->url as $url) {
            $locs[] = (string) $url->loc;
        }

        $this->assertContains(config('app.url').'/calculators/'.$calculator->slug, $locs);
        $this->assertContains(config('app.url').'/reference-ranges/'.$range->slug, $locs);
        $this->assertContains(config('app.url').'/interpretation/'.$interpretation->slug, $locs);
        $this->assertContains(config('app.url').'/knowledge/'.$knowledge->slug, $locs);
    }

    public function test_sitemap_excludes_non_published_resources(): void
    {
        $draft = Calculator::factory()->create(['status' => ResourceStatus::DRAFT]);
        $archived = KnowledgeResource::factory()->create(['status' => ResourceStatus::ARCHIVED]);
        $obsolete = InterpretationResource::factory()->create(['status' => ResourceStatus::OBSOLETE]);

        $this->artisan('sitemap:generate');

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringNotContainsString('/calculators/'.$draft->slug, $content);
        $this->assertStringNotContainsString('/knowledge/'.$archived->slug, $content);
        $this->assertStringNotContainsString('/interpretation/'.$obsolete->slug, $content);
    }

    public function test_sitemap_excludes_protected_routes(): void
    {
        $this->artisan('sitemap:generate');

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringNotContainsString('/admin', $content);
        $this->assertStringNotContainsString('/auth', $content);
        $this->assertStringNotContainsString('/api', $content);
        $this->assertStringNotContainsString('/search', $content);
    }

    public function test_sitemap_includes_lastmod_dates(): void
    {
        $calculator = Calculator::factory()->create([
            'status' => ResourceStatus::PUBLISHED,
            'updated_at' => now()->subDay(),
        ]);

        $this->artisan('sitemap:generate');

        $xml = simplexml_load_file(public_path('sitemap.xml'));

        $found = false;
        foreach ($xml->url as $url) {
            if ((string) $url->loc === config('app.url').'/calculators/'.$calculator->slug) {
                $this->assertNotEmpty((string) $url->lastmod);
                $found = true;
            }
        }

        $this->assertTrue($found);
    }

    public function test_sitemap_is_served_at_public_url(): void
    {
        $this->artisan('sitemap:generate');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }
}
