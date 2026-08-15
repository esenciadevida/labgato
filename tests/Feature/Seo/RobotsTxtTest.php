<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_is_served_as_plain_text(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $this->assertStringStartsWith('text/plain', $response->headers->get('Content-Type'));
    }

    public function test_robots_txt_disallows_admin(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin', false);
    }

    public function test_robots_txt_disallows_auth(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /auth', false);
    }

    public function test_robots_txt_disallows_api(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /api', false);
    }

    public function test_robots_txt_disallows_search(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /search', false);
    }

    public function test_robots_txt_includes_sitemap_reference(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: '.config('app.url').'/sitemap.xml', false);
    }
}
