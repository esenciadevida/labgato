<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_file_exists(): void
    {
        $this->assertFileExists(public_path('robots.txt'));
    }

    public function test_robots_txt_disallows_admin(): void
    {
        $content = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /admin', $content);
    }

    public function test_robots_txt_disallows_auth(): void
    {
        $content = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /auth', $content);
    }

    public function test_robots_txt_disallows_api(): void
    {
        $content = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /api', $content);
    }

    public function test_robots_txt_includes_sitemap(): void
    {
        $content = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Sitemap:', $content);
    }
}
