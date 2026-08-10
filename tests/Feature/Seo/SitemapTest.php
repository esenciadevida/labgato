<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(public_path('sitemap.xml'));
        parent::tearDown();
    }

    public function test_sitemap_command_generates_file(): void
    {
        $this->artisan('sitemap:generate')
            ->assertExitCode(0);

        $this->assertFileExists(public_path('sitemap.xml'));
    }

    public function test_sitemap_includes_home_page(): void
    {
        $this->artisan('sitemap:generate');

        $content = file_get_contents(public_path('sitemap.xml'));
        $this->assertStringContainsString('/', $content);
    }

    public function test_sitemap_excludes_admin_routes(): void
    {
        $this->artisan('sitemap:generate');

        $content = file_get_contents(public_path('sitemap.xml'));
        $this->assertStringNotContainsString('/admin', $content);
    }

    public function test_sitemap_excludes_auth_routes(): void
    {
        $this->artisan('sitemap:generate');

        $content = file_get_contents(public_path('sitemap.xml'));
        $this->assertStringNotContainsString('/auth', $content);
    }

    public function test_sitemap_excludes_api_routes(): void
    {
        $this->artisan('sitemap:generate');

        $content = file_get_contents(public_path('sitemap.xml'));
        $this->assertStringNotContainsString('/api', $content);
    }
}
