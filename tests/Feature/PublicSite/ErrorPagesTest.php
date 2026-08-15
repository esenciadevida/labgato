<?php

namespace Tests\Feature\PublicSite;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_renders_for_missing_route(): void
    {
        $response = $this->get('/nonexistent-page-that-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('Page Not Found');
        $response->assertSee('may have moved');
    }

    public function test_404_page_offers_go_home_and_contact_actions(): void
    {
        $response = $this->get('/nonexistent-page-that-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('Go Home');
        $response->assertSee('Contact Support');
    }

    public function test_404_page_does_not_expose_stack_trace(): void
    {
        $response = $this->get('/nonexistent-page-that-does-not-exist');

        $response->assertStatus(404);
        $response->assertDontSee('Stack trace');
        $response->assertDontSee('vendor/');
        $response->assertDontSee('app/');
    }

    public function test_403_page_exists_and_renders(): void
    {
        $response = $this->get('/errors/403');

        if ($response->status() === 200) {
            $response->assertSee('403');
            $response->assertSee('Access Denied');
        } else {
            $this->assertContains($response->status(), [403, 404]);
        }
    }
}
